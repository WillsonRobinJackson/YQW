/**
 * Created by leijj on 16/4/20.
 * 高德地图 公共方法
 */
/** 关键字搜索自动匹配*/
function attrAutocomplete(callback){
    AMap.plugin(['AMap.Autocomplete'],function(){
        var autoOptions = {
            city: getCookie('chooseCity') //城市，默认全国
            //input: "keyword"//使用联想输入的input的id
        };
        var autocomplete = new AMap.Autocomplete(autoOptions);
        $('#keyword').bind('input propertychange', function(){
            var value = document.getElementById('keyword').value;
            if(value) {
                $('.clear-content').show();
            } else {
                $('.clear-content').hide();
            }
            autocomplete.search(value, function(status, result){
                $('#completeAddr').html('');
                if(status == 'complete' && result.tips && result.tips.length > 0){
                    var autoStr = '<ul class="AMAP-result">';
                    var tips = result.tips;
                    for(var i = 0; i < tips.length; i++){
                        var district = tips[i].district;
                        var location = tips[i].location;
                        var name = tips[i].name;
                        if(location) {
                            autoStr += "<li class='auto-item' onclick='" + callback + "("+JSON.stringify(tips[i]) +")'>" + name + "<span class='city'>" + district + "</span>" + "</li>";
                        }
                    }
                    autoStr += '</ul>';
                    $('#completeAddr').show();
                    $('#completeAddr').html(autoStr);
                    $(".amap-sug-result").css({left:0})
                }
            })
        });
    });
}
/** 获取当前位置经纬度,反向解析位置信息*/
function getWxLocation(){
    if(!isWeiXin()) return;
    wx.ready(function(){
        wx.getLocation({
            type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
            success: function (res) {
                var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                var lnglatXY = [];
                lnglatXY.push(longitude);
                lnglatXY.push(latitude);
                setLocalStorageValue("autoLng", longitude || '', true);
                setLocalStorageValue("autoLat", latitude || '', true);
                var geocoder = new AMap.Geocoder({
                    radius: 1000,
                    extensions: "all"
                });
                geocoder.getAddress(lnglatXY, function(status, result) {
                    if (status === 'complete' && result.info === 'OK') {
                        //alert(result.regeocode.formattedAddress);
                        setLocalStorageValue("autoAddr", result.regeocode.formattedAddress || '', true);
                    }
                });
            }
        });
    });
}
/** 调用浏览器定位服务*/
var map, geolocation;
function getBrowserLocation(){
    map = new AMap.Map('container', {
        resizeEnable: true
    });
    map.plugin('AMap.Geolocation', function() {
        geolocation = new AMap.Geolocation({
            enableHighAccuracy: true,//是否使用高精度定位，默认:true
            timeout: 10000,          //超过10秒后停止定位，默认：无穷大
            buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
            zoomToAccuracy: true,      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
            buttonPosition:'RB'
        });
        map.addControl(geolocation);
        geolocation.getCurrentPosition();
        AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
        AMap.event.addListener(geolocation, 'error', onError);      //返回定位出错信息
    });
}

//解析定位结果
function onComplete(data) {
    var lnglatXY = [];
    lnglatXY.push(data.position.getLng());
    lnglatXY.push(data.position.getLat());
    setLocalStorageValue("autoLng", data.position.getLng() || '', true);
    setLocalStorageValue("autoLat", data.position.getLat() || '', true);
    var geocoder = new AMap.Geocoder({
        radius: 1000,
        extensions: "all"
    });
    geocoder.getAddress(lnglatXY, function(status, result) {
        if (status === 'complete' && result.info === 'OK') {
            //alert("1"+result.regeocode.formattedAddress)
            setLocalStorageValue("autoAddr", result.regeocode.formattedAddress || '', true);
        }
    });
}
//解析定位错误信息
function onError(data) {
    console.log(JSON.stringify(data));
}

//正向地理编码
function geocoder(addr) {
    var geocoder = new AMap.Geocoder({
        city: "010", //城市，默认：“全国”
        radius: 1000 //范围，默认：500
    });
    //地理编码,返回地理编码结果
    geocoder.getLocation(addr, function(status, result) {
        if (status === 'complete' && result.info === 'OK') {
            var geocode = result.geocodes;
            for (var i = 0; i < geocode.length; i++) {
                var lng = geocode[i].location.getLng();
                var lat = geocode[i].location.getLat();
                var lnglatXY = [];
                lnglatXY.push(lng);
                lnglatXY.push(lat);
                return lnglatXY;
            }
        }
    });
}