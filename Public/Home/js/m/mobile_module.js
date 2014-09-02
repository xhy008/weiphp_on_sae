// JavaScript Document by jacy
/**
 定义基本常量
*/
var RESULT_SUCCESS = 'success';
var RESULT_FAIL = 'fail';
var WeiPHP_RAND_COLOR = ["#ff6600","#ff9900","#99cc00","#33cc00","#0099cc","#3399ff","#9933ff","#cc3366","#333333","#339999","#ff6600","#ff9900","#99cc00","#33cc00","#0099cc","#3399ff","#9933ff","#cc3366","#333333","#339999","#ff6600","#ff9900","#99cc00","#33cc00","#0099cc","#3399ff","#9933ff","#cc3366","#333333","#339999"];

/***/
(function(){
	//异步请求提交表单
	//提交后返回格式json json格式 {'result':'success|fail',data:{....}}
	function doAjaxSubmit(form,callback){
		$.Dialog.loading();
		$.ajax({
			data:form.serializeArray(),
			type:'post',
			dataType:'json',
			url:form.attr('action'),
			success:function(data){
				$.Dialog.close();
				callback(data);
				}
			})
	}
	
	function initFixedLayout(){
		var navHeight = $('#fixedNav').height();
		$('#fixedContainer').height($(window).height()-navHeight);	
	}
	//banner
	function banner(isAuto,delayTime){
		var screenWidth = $('.container').width();
		var count = $('.banner li').size();
		$('.banner ul').width(screenWidth*count);
		$('.banner ul').height(screenWidth/2);
		$('.banner').height(screenWidth/2);
		$('.banner li').width(screenWidth).height(screenWidth/2);
		$('.banner li img').width(screenWidth).height(screenWidth/2);
		$('.banner li .title').css({'width':'98%','padding-left':'2%'})
		// With options
		$('.banner li .title').each(function(index, element) {
            $(this).text($(this).text().length>15?$(this).text().substring(0,15)+" ...":$(this).text());
        });
		var flipsnap = Flipsnap('.banner ul');
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			$('.identify em').eq(ev.newPoint).addClass('cur').siblings().removeClass('cur');
		}, false);
		$('.identify em').eq(0).addClass('cur')
		if(isAuto){
			var point = 1;
			setInterval(function(){
				//console.log(point);
				flipsnap.moveToPoint(point);
				$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
				if(point+1==$('.banner li').size()){
					point=0;
				}else{
					point++;
					}
				
				},delayTime)
		}
	}
	function squarePicSlide(isAuto,delayTime,width,height,prevBtn,nextBtn){
		var count = $('.banner li').size();
		$('.banner ul').width(width*count);
		$('.banner ul').height(height);
		$('.banner').height(height);
		$('.banner li').width(width).height(height);
		$('.banner li img').width(width).css('min-height',height);
		$('.banner li .title').css({'width':'98%','padding-left':'2%'})
		// With options
		$('.banner li .title').each(function(index, element) {
            $(this).text($(this).text().length>15?$(this).text().substring(0,15)+" ...":$(this).text());
        });
		var flipsnap = Flipsnap('.banner ul');
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			$('.identify em').eq(ev.newPoint).addClass('cur').siblings().removeClass('cur');
		}, false);
		$('.identify em').eq(0).addClass('cur');
		var point = 0;
		if(isAuto){
			
			setInterval(function(){
				//console.log(point);
				flipsnap.moveToPoint(point);
				},delayTime)
		}
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			point = ev.newPoint;
			$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
		}, false);
		$(prevBtn).click(function(){
			 if(flipsnap.hasPrev()){
				flipsnap.toPrev();
				point = point-1;
			 }else{
				flipsnap.moveToPoint(count-1);
				point = count-1;
				}
			$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
			});
		$(nextBtn).click(function(){
			 if(flipsnap.hasNext()){
				flipsnap.toNext();
				point = point+1;
			 }else{
				flipsnap.moveToPoint(0);
				point = 0;
				}
			$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
			
			});
	}
	//随机颜色
	function setRandomColor(selector){
		$(selector).each(function(index, element) {
			$(this).css('background-color',WeiPHP_RAND_COLOR[index]);
		});;
	}
	var WeiPHP = {
		doAjaxSubmit:doAjaxSubmit,
		setRandomColor:setRandomColor,
		initBanner:banner,
		squarePicSlide:squarePicSlide,
		initFixedLayout:initFixedLayout
	};
	$.extend({
		WeiPHP: WeiPHP
	});
})();

/*
*/
$(function(){
	$('.toggle_list .title').click(function(){
		$(this).parents('li').toggleClass("toggle_list_open");
		})
	$('.top_nav_a').click(function(){
		if(!$(this).hasClass('active')){
				$(this).next().show();
				$(this).addClass('active')
			}else{
				$(this).next().hide();
				$(this).removeClass('active')
				}
		});
	
	//打开成员详情
	$('.member_item').click(function(){
		var detail = $(this).find('.detail').html();
		var dialogHtml = $('<div class="member_dialog"><span class="close"></span><div>'+detail+'</div></div>');
		var closeHtml = $('.close',dialogHtml);
		closeHtml.click(function(){
			$.Dialog.close();
			});
		$.Dialog.open(dialogHtml);
		})
	//考试选择效果
	$(".testing li input[type='radio']").change(function(){
		var $icon = $(this).parent("label").find(".icon");
		if(!$icon.hasClass("selected"))$icon.addClass('selected');
		$(this).parents("li").siblings().find(".icon").removeClass("selected");
		
	});
	$(".testing li input[type='checkbox']").change(function(){
		var $icon = $(this).parent("label").find(".icon");
		console.log($(this).is(":checked"));
		if($(this).is(":checked")){
			$icon.addClass('selected');
			}else{
				$icon.removeClass('selected');
				}
		
		
		
	});
	$('.class_item .more').click(function(){
			$(this).parent().find('.summary').toggle();
			$(this).parent().find('.desc_all').toggle();
			$(this).html()=="查看更多"?$(this).html("收起"):$(this).html("查看更多");
		});
	//返回
	$(".top_back_btn").click(function(){
		var href = $(this).attr('href');
		if(href=='javascript:void(0);'||href==''||href=='###'||href=='#')	history.back(-1);
	});
	
})