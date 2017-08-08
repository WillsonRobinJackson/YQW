/* ============================================================
 * jquery.jobslist.js  职位搜索列表页面js集合
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

	// 搜索类型切换
	$('.J_sli').click(function() {
		$(this).addClass('select').siblings().removeClass('select');
		var indexValue = $('.J_sli').index(this);
		var typeValue = $.trim($(this).data('type'));
		$('input[name="search_type"]').val(typeValue);
	});

	// 收起、展开筛选条件
	foldAction('.J_showbtn', '.J_so_condition');
	function foldAction(trigger, performer) {
		$(trigger).click(function() {
			$(this).addClass('none').siblings().removeClass('none');
			var indexValue = $(trigger).index(this);
			if (indexValue) {
				$(performer).slideUp();
			} else {
				$(performer).slideDown();
			}
		})
	}
	$('.J_showJobConditions').die().live('click', function(event) {
		$(this).addClass('none').siblings().removeClass('none');
		var indexValue = $('.J_showJobConditions').index(this);
		if (indexValue) {
			$('.for_up').slideDown();
		} else {
			$('.for_up').slideUp();
		}
	});

	// 列表详细和简易切换
	$('.J_detailList').click(function() {
		$(this).addClass('select').siblings('.J_detailList').removeClass('select');
		var indexValue = $('.J_detailList').index(this),
			type = $(this).attr('show_type');
		if (indexValue) {
			$('.J_allListBox').find('.detail').hide();
			$('.J_allListBox').find('.J_jobsStatus').addClass('show');
		} else {
			$('.J_allListBox').find('.detail').show();
			$('.J_allListBox').find('.J_jobsStatus').removeClass('show');
		}
		$.getJSON(qscms.root + '?m=Home&c=AjaxCommon&a=list_show_type',{action:'jobs',type:type});
	});

	// 周边职位和热门职位切换
	$('.J_job_hotnear').click(function() {
		$(this).addClass('select').siblings('.J_job_hotnear').removeClass('select');
		var indexValue = $('.J_job_hotnear').index(this);
		$('.J_job_hotnear_show').removeClass('show');
		$('.J_job_hotnear_show').eq(indexValue).addClass('show');
	});

	// 列表详细展开收起
	$('.J_jobsStatus').click(function(){
		if($(this).hasClass('show')){
			$(this).removeClass('show');
			$(this).closest('.J_jobsList').find('.detail').show();
		}else{
			$(this).addClass('show');
			$(this).closest('.J_jobsList').find('.detail').hide();
		}
	});

	// 全选、反选
	$('.J_allSelected').click(function() {
		var isChecked = $(this).hasClass('select');
		var listArray = $('.J_allListBox .J_allList');
		if (isChecked) {
			$(this).removeClass('select');
			$.each(listArray, function(index, val) {
				$(this).removeClass('select');
			});
			$('.J_jobsList').removeClass('select');
		} else {
			$(this).addClass('select');
			$.each(listArray, function(index, val) {
				$(this).addClass('select');
			});
			$('.J_jobsList').addClass('select');
		}
		
	});
	$('.J_allList').click(function(){
		var isChecked = $(this).hasClass('select');
		if (isChecked) {
			$(this).removeClass('select');
			$(this).closest('.J_jobsList').removeClass('select');
			$('.J_allSelected').removeClass('select');
		} else {
			$(this).addClass('select');
			$(this).closest('.J_jobsList').addClass('select');
			var listArray = $('.J_allListBox .J_allList');
			var listCheckedArray = $('.J_allListBox .J_allList.select');
			if (listArray.length == listCheckedArray.length) {
				$('.J_allSelected').addClass('select');
			}
		}
	});

	// 申请、收藏职位
	jobSomething('.J_applyForJob', '申请成功！', true);
	jobSomething('.J_collectForJob', '收藏成功！', false);

	function jobSomething (trigger, successMsg, iscreate) {
		$(trigger).click(function() {
			var batch = eval($(this).data('batch'));
			var url = $(this).data('url');
			var jidValue = '';
			if (batch) { // 是否是批量
				if (listCheckEmpty()) {
					disapperTooltip('remind','您还没有选择职位！');
					return false;
				} else {
					var listCheckedObjs = $('.J_allListBox .J_allList.select');
					var jidArray = new Array();
					$.each(listCheckedObjs, function(index, val) {
						jidArray[index] = $(this).closest('.J_jobsList').data('jid');
					});
					jidValue = jidArray.join(',');
				}
			} else {
				jidValue = $(this).closest('.J_jobsList').data('jid');
			}
			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: {jid: jidValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if(data.data.html){
						$(this).dialog({
							title: '申请职位',
							border: false,
							content:data.data.html
						});
					} else {
						disapperTooltip('success', successMsg);
					}
				}
				else if(data.data==1){
					location.href=qscms.root+"?m=Home&c=Personal&a=resume_add";
				}
				 else {
					if (eval(data.dialog)) {
						var qsDialog = $(this).dialog({
			        		loading: true,
							footer: false,
							header: false,
							border: false,
							backdrop: false
						});
						if (iscreate) { // 申请职位
                            if (eval(qscms.smsTatus)) {// 是否开启短信
                                var creatsUrl = qscms.root + '?m=Home&c=AjaxPersonal&a=resume_add_dig';
                                $.getJSON(creatsUrl,{jid:jidValue}, function(result){
                                    if(result.status==1){
                                        qsDialog.hide();
                                        var qsDialogSon = $(this).dialog({
                                            content: result.data.html,
                                            footer: false,
                                            header: false,
                                            border: false
                                        });
                                        qsDialogSon.setInnerPadding(false);
                                    } else {
                                        qsDialog.hide();
                                        disapperTooltip("remind", result.msg);
                                    }
                                });
                            } else {
                                var loginUrl = qscms.root + '?m=Home&c=AjaxCommon&a=get_login_dig';
                                $.getJSON(loginUrl, function(result){
                                    if(result.status==1){
                                        qsDialog.hide();
                                        var qsDialogSon = $(this).dialog({
                                            title: '会员登录',
                                            content: result.data.html,
                                            footer: false,
                                            border: false
                                        });
                                        qsDialogSon.setInnerPadding(false);
                                    } else {
                                        qsDialog.hide();
                                        disapperTooltip("remind", result.msg);
                                    }
                                });
                            }
						} else {
							var loginUrl = qscms.root + '?m=Home&c=AjaxCommon&a=get_login_dig';
							$.getJSON(loginUrl, function(result){
					            if(result.status==1){
									qsDialog.hide();
									var qsDialogSon = $(this).dialog({
										title: '会员登录',
										content: result.data.html,
										footer: false,
										border: false
									});
					    			qsDialogSon.setInnerPadding(false);
					            } else {
					            	qsDialog.hide();
					                disapperTooltip("remind", result.msg);
					            }
					        });
						}
					} else {
						disapperTooltip("remind", data.msg);
					}
				}
			})
		});
	}

	// 判断列表中是否有选中的项目
	function listCheckEmpty() {
		var listCheckedArray = $('.J_allListBox .J_allList.select');
		if (listCheckedArray.length) {
			return false;
		} else {
			return true;
		}
	}

	// 关键字改变，搜索条件清空
	$('#ajax_search_location').submit(function(){
		var nowKeyValue = $.trim($('input[name="key"]').val());
		var orgKeyValue = $.trim($('input[name="key"]').data('original'));
		if(nowKeyValue.length && nowKeyValue.length<2){
			disapperTooltip("remind",'关健字长度需大于2个字！');
			return !1;
		}
		if (!(nowKeyValue == orgKeyValue)) {
			$('.J_forclear').val('');
		}
		var post_data = $('#ajax_search_location').serialize();
		if(qscms.keyUrlencode==1){
			post_data = encodeURI(post_data);
		}
		$.post($('#ajax_search_location').attr('action'),post_data,function(result){
			window.location=result.data;
		},'json');
		return false;
	});
	
}(window.jQuery);