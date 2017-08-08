(function($){
	/* 投递简历 电话联系 对应相应的职位 */
    function load_jobs(index,utype)
    {
        var _job = $($(".posit_details").get(index));
        if(utype=="2")
        {
        	$(".job_btn_list > .add").attr("jobs_id", _job.attr("jobs_id"));
	        $(".job_btn_list > .add").attr("jobs_name", _job.attr("jobs_name"));
	        $(".job_btn_list > .add").attr("company_id", _job.attr("company_id"));
	        $(".job_btn_list > .add").attr("company_name", _job.attr("company_name"));
	        $(".job_btn_list > .add").attr("company_uid", _job.attr("company_uid"));
        }
        else
        {
        	$(".job_btn_list > .add").attr("href",qscms.root+'?m=Mobile&c=Members&a=login');
         // $(".job_btn_list > .tel").attr("href","login.php");
        }
        
        var isTel = $("#jobs_phone_code").attr('code');
        if (isTel > 0) {
          $(".job_btn_list > .tel").attr("pho", "tel:"+_job.attr("jobs_tel"));
        };
    }
	/* 滑动 效果 */
	function swipe_self()
	{
        var winWidth = window.innerWidth;
        var winHeight = window.innerHeight;
        var screenFix = function(){
          $("#poster_contain").css({
            width:winWidth+"px",
            height:winHeight+"px"
          });
        };
        screenFix();
        var orientationEvent = ('onorientationchange' in window) ? 'orientationchange' : 'resize';
        window.addEventListener(orientationEvent, function() {
          window.setTimeout(function(){
            screenFix();
          }, 600);
        }, false);

        $(".layer, .wx_layer").on("click", function(){
          $(this).hide();
        });
        var _indexSwipeUp=0;
        var _indexSwipe=0;
        // 滑动
        $("#poster_contain").swipeUp({
          index:_indexSwipeUp,
          childrenClass:".poster_wrap",
          init:function()
          {
            $(".arrow_con").addClass("show");
            setTimeout(function(){
              $($("#poster_contain .poster_wrap").get(_indexSwipeUp)).addClass("focus");
            }, 300);
          },
          afterSwipe:function(index)
          {
            var _pw = $("#poster_contain .poster_wrap");
            _pw.removeClass("focus");
            $(_pw.get(index)).addClass("focus");
            if(index == _pw.length-1){
            $(".arrow_con").removeClass("show");
            }else{
            $(".arrow_con").addClass("show");
            }

            var rewardHomeCon=$('.reward_home_con'),
            rewardHomeConLength=rewardHomeCon.length; 
            if(rewardHomeConLength>0){
              if(rewardHomeCon.hasClass("homt_active")){
                rewardHomeCon.removeClass("homt_active");
              }
              var rewardPlus=$('.reward_plus');
              if(rewardPlus.hasClass("plus_animate")){
                rewardPlus.removeClass("plus_animate");
              }
            }

            if(index == 2){
              welfInterval = setInterval(function(){
              var _s = parseInt(Math.random()*6);
              $(".welf_bg > div").css({"-webkit-animation":"none"});
              $($(".welf_bg > div").get(_s)).css({"-webkit-animation":"fuli"+(_s%2)+" 1s ease-out"});
              }, 1000);
            }else{
              if(typeof(welfInterval) != "undefined"){
              clearInterval(welfInterval);
              }
              $(".welf_bg > div").css({"-webkit-animation":"none"});
            }
          }
        });
        // 职位滑动
        var _width = winWidth;
        _width = _width-30*2;
        $(".posit_details").css({width:_width+"px"})
        $(".posit_details img").css({width:_width+"px"})
        var _ulWidth = $(".posit_details").length * (_width+15) + 15;

        $(".posit_list_ul").css({width:_ulWidth+"px"});

        $(".posit_list_ul").swipe({
          index:_indexSwipe,
          width:_width + 15,
          afterSwipe:function(index)
          {
            load_jobs(index,utype);
          }
        });
        //分享按钮
        $('.praise_share_btn').on('click',function(){
          var agent = navigator.userAgent.toLowerCase();
          if(agent.indexOf('micromessenger') < 0)
          {
            share_();
          }
          else
          {
            share();
          }
        });
        $(".layer, .wx_layer").on("click", function(){
          $(this).hide();
        });
        
    };
    // 点赞
    function praise(company_id)
    {
    	$(".praise_btn_click").on('click',function(event)
        {
          setCookie('praise_'+company_id+'','1');
          if($(".praise_btn").hasClass('praise_btn_click')){
            $.getJSON(qscms.root+"?m=Mobile&c=Wzp&a=com_praise_click",{id:company_id},function(result){
              if(result.status==1){
                $("#praise_num").html(result.data);
                $(".praise_btn").addClass('on').removeClass('praise_btn_click');
              }
            });
          }
        });
    }
    function setCookie(name,value) 
    { 
        var Days = 30; 
        var exp = new Date(); 
        exp.setTime(exp.getTime() + Days*24*60*60*1000); 
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString(); 
    } 

    //读取cookies 
    function getCookie(name) 
    { 
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
     
        if(arr=document.cookie.match(reg))
     
            return unescape(arr[2]); 
        else 
            return null; 
    } 
    function delCookie(name) 
    { 
        var exp = new Date(); 
        exp.setTime(exp.getTime() - 1); 
        var cval=getCookie(name); 
        if(cval!=null) 
            document.cookie= name + "="+cval+";expires="+exp.toGMTString(); 
    }
        
    /* 延时加载 time */
   	function loading(time){
   		var time=time?time:1000;
    	setTimeout(function(){
	      $("#load").hide();
	      swipe_self();
	    }, time);
    };
    /* 申请职位 弹出框 */
    function showFloatBox()
    {
        var width = window.innerWidth;
        var height = window.innerHeight;
        $("body").prepend("<div class=\"menu_bg_layer\"></div>");
        $(".menu_bg_layer").css({ height:height+'px',width: width+"px", position: "absolute",left:"0", top:"0","z-index":"999","background-color":"#000000"});
        $(".menu_bg_layer").css("opacity",0.3);
    };
    /* 申请职位 操作 */
    function jobs_apply()
    {
    	$("#jobs_apply").on('click',function()
      {
          var href= $(this).attr("href")
          if(href=="javascript:;")
          {
              var jobs_id = $(this).attr("jobs_id");
              if(qscms.resume_id){
                  $.getJSON(qscms.root+'?m=Mobile&c=AjaxPersonal&a=resume_apply',{jid:jobs_id},function(result){
                      if(result.status==1){
                        alert(result.msg);
                      }else{
                        alert(result.msg);
                      }
                  },'json');
                }else{
                  alert('请选择简历');
                }
          }

      });
    };
	
	
		$("#jobs_phone").on('click',function() {
      var href= $(this).attr("pho");
      if(href){
        var result = href.replace('tel:','');
      }else{
        var result = '';
      }
      if(result=='') {
          var shopping = document.getElementById("jobs_phone");
          var phone =  shopping.getAttribute("phone");
          showFloatBox();
          $("#jobs_phone_menu").show();
          var height = window.innerHeight;
          var choose_menu_h = document.getElementById('jobs_phone_menu').offsetHeight;
          var top_ = (height-choose_menu_h)/2;
          $("#jobs_phone_menu").css("top",top_+"px");
          $("#jobs_phone_menu").css({"opacity":1,"z-index":9999});
          $(".but_right,.menu_bg_layer").on('click', function(event) {
             $("#jobs_phone_menu").hide();
             $(".menu_bg_layer").remove();
          });
      } else {
        window.location.href=href;
      }
	  });
	
    /* 左侧 菜单*/
    function left_menu()
    {
    	// 显示菜单
    	$(".nav_btn_con").on("touchstart", function(){
			$(".reward_manager_list_con, .reward_manager_list_con_bg").addClass("on");
		});
		// 隐藏菜单
		$(".reward_manager_list_con_bg").on("touchstart", function(){
			$(".reward_manager_list_con, .reward_manager_list_con_bg").removeClass("on");
		});
    };
    /* 显示分享 覆盖层 */
    function share(){
      $(".wx_layer").show();
      
	};

	function share_(){
    $(".layer").show();
	};
	
	loading();
	left_menu();
	load_jobs(0,utype);
	praise(company_id);
    jobs_apply();
  if(getCookie('praise_'+company_id+'')==1)
  {
    $(".praise_btn").addClass('on').removeClass('praise_btn_click');
  }
})(jq);