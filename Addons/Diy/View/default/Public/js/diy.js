/*
	@jacy
*/
var currentTarget = null;
var currentTargetParam = {
	 widget_name:"",
	 paramUrl:"",
	 htmlUrl : "",
	 saveUrl : "",
	 templateId : ""
	};
var allFormData = new Array();
function addFunction(_this){
		currentTarget = $(_this).parents('.layout_item');
		if(currentTarget.hasClass('function_edit')){
			//console.log('aaa')
				var widget_name = currentTarget.data("name");
				var paramUrl = currentTarget.data("set");
				var htmlUrl = currentTarget.data("html");
				var saveUrl = currentTarget.data("save");
				$('body').addClass('no-scroll');
				openSetDailog(widget_name,paramUrl,htmlUrl,saveUrl);
			}else{
				//console.log('bbb')
				$('#functionModule').show();
				
			}
		
		}
function deleteFunction(_this){
	if(confirm('确定删除？')){
		var deleteTarget = $(_this).parents('.layout_item');
		deleteTarget.removeClass('function_edit');
		deleteTarget.removeAttr('data-set');
		deleteTarget.removeAttr('data-save');
		deleteTarget.removeAttr('data-html');
		deleteTarget.removeAttr('data-param');
		deleteTarget.removeAttr('data-id');
		deleteTarget.removeAttr('data-name');
		deleteTarget.find('.item_content').html('');
	}
}
function removeLayout(_this){
	if(confirm('确定删除？')){
		$(_this).parents('.layout_row').remove();
	}
		}
function map(key,value){
	var o = new Object();
		o.name = key;
		o.value = value;
	return o;
	}
//初始化每个item
function initEachItemHtml(type){
		var cols = [];
		var reg = /\:/;
		if(!reg.test(type)){
			cols[0] = 1;
		}else{
			cols = type.split(":");
		}
		var itemCount = cols.length;
		var itemListHtml = "";
		var singleItemHtml ="<div class='layout_item'><div class='item_content'></div></div>";
		for(var i=0;i<itemCount;i++){
			itemListHtml = itemListHtml + singleItemHtml;
			}
		return itemListHtml;
	}
//初始化布局
function initItemLayoutAndAction($html,type,isAction){
	var cols = [];
	var reg = /\:/;
	if(!reg.test(type)){
		cols[0] = 1;
	}else{
		cols = type.split(":");
		}
	var itemCount = cols.length;
	var ww = 0; 
	for(var i=0;i<itemCount;i++){
		ww = ww + parseInt(cols[i]);
	}
	var layoutActionHtml = "<div class='layout_action'><a href='javascript:;' onclick='removeLayout(this);' class='delete_layout' title='删除布局'></a></div>";
	var itemActionHtml = "<div class='module_action'><a href='javascript:;' onclick='addFunction(this);' class='item_add' title='功能模块设置'><span class='add'></span></a><a href='javascript:;' onclick='deleteFunction(this);' class='item_delete' title='删除功能模块'><span class='delete'></span></a></div>";
	$html.find('.layout_item').each(function(i, element) {
			//初始化宽度
			//console.log(ww);
			//console.log(cols[i]/ww);
			$(this).css({"width":(parseInt(cols[i])/ww)*100+"%"});
			//添加操作
			if(isAction){
				$(this).append($(itemActionHtml));
			}
		});
	if(isAction){
		$html.append($(layoutActionHtml));
	}
}
//通用配置框
function openSetDailog(widget_name,url,htmlUrl,saveUrl){
		$('#setDialog .content').html("");
		$('#setDialog').show();
		$('#setDialog').addClass('dialog_loading');
		var templateId = new Date().getTime()+WeiPHPMid;
		var paramData ="";
		if(currentTarget.data('id')!=undefined && currentTarget.data('id')!=""){
			templateId = currentTarget.data('id');
			if(url.indexOf("?")==-1){
				paramData = "?page_id="+DiyPageId+"&widget_id="+templateId;
			}else{
				paramData = "&page_id="+DiyPageId+"&widget_id="+templateId;
				}
		}
		$('.dialog_layer').show();
		$('body').addClass('no-scroll');
		$('#setDialog #paramIframe').attr('src',url+paramData).show();
		currentTargetParam.widget_name =  widget_name;
		currentTargetParam.url =  url;
		currentTargetParam.htmlUrl =  htmlUrl;
		currentTargetParam.saveUrl =  saveUrl;
		currentTargetParam.templateId = templateId;
	}
//banner
function layoutBanner(isAuto,delayTime,$banner){
	var screenWidth = $('.layout_item').width();
	var count = $($banner).find('li').size();
	$($banner).find('ul').width(screenWidth*count);
	$($banner).find('ul').height(screenWidth/2);
	$($banner).height(screenWidth/2);
	$($banner).find('li').width(screenWidth).height(screenWidth/2);
	$($banner).find('li img').width(screenWidth).height(screenWidth/2);
	// With options
	$($banner).find('li .title').each(function(index, element) {
		 $(this).text($(this).text().length>15?$(this).text().substring(0,15)+" ...":$(this).text());
	});
	var flipsnap = Flipsnap($banner+' ul');
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
$(function() {
	if($( "#phoneArea" ).sortable){
		$( "#phoneArea" ).sortable({
		  revert: true,
		  receive: function( event, ui ) {
			 
			  }
		});
	}
	$('.add_layout').click(function(){
		$('body').addClass('no-scroll');
		$('#layoutModule').show();
		});
	
	$('.dialog_close,.dialog_layer').click(function(){
		$('body').removeClass('no-scroll');
		$('.diy_dialog').hide();
		$('.dialog_layer').hide();
		currentTarget = null;
		})
	$('#changeBg').click(function(){
		$('body').addClass('no-scroll');
		$('#bgModule').show();
		});
	$( "#layoutModule .layout" ).each(function(index, element) {
        $(this).click(function(){
				var type = $(this).data("type");
				$('#layoutModule').hide();
				$('dialog_layer').hide();
			$('body').removeClass('no-scroll');
				var $html = $("<div class='layout_row' data-type='"+type+"'>"+
					initEachItemHtml(type)+
					"</div>");
				initItemLayoutAndAction($html,type,true);	
				$('#phoneArea').append($html);
			})
    });
	$( "#layoutModule #confirmLayout" ).click(function(){
		var type = $('#layoutModule #selfType').val();
		var reg= /\:/;
		if((type!="" && reg.test(type))||type==1){
			$('.dialog_layer').hide();
			$('body').removeClass('no-scroll');
			$('#layoutModule').hide();
			var $html = $("<div class='layout_row' data-type='"+type+"'>"+
				initEachItemHtml(type)+
				"</div>");
			initItemLayoutAndAction($html,type,true);	
			$('#phoneArea').append($html);
		}else{
		 	alert("请输入合法的布局参数");	
		}
	})
	$( "#functionModule .function" ).each(function(index, element) {
        $(this).click(function(){
				var widget_name = $(this).data("name");
				var paramUrl = $(this).data("set");
				var htmlUrl = $(this).data("html");
				var saveUrl = $(this).data("save");
				$('#functionModule').hide();
				openSetDailog(widget_name,paramUrl,htmlUrl,saveUrl);
				
			})
    });
	//背景
	$( "#bgModule #confirm" ).click(function(){
				$('#bgModule').hide();
				$('.dialog_layer').hide();
				$('body').removeClass('no-scroll');
				if($("#bgModule #bg_cover_id_imgId").val()!=""){
					$('.area').css("background-image","url("+$("#bgModule .upload-pre-item").find('img').attr('src')+")");
					$('.area').css({'background-size':'100% 100%',});
					$('#phoneArea').attr('data-bgid',$("#bgModule #bg_cover_id_imgId").val());
				}else{
					$('#phoneArea').css("background-image","");
					$('#phoneArea').attr('data-bgid',0);
				}
    });
	//保存排版
	$( "#saveLayout").click(function(){
		if($(this).hasClass('loading'))return;
		$(this).addClass("loading").text("loading...");
		var layouts = [];
		$('.layout_row').each(function(i, e1) {
            var type = $(this).data('type');
			var layoutItems = [];
			$(this).find('.layout_item').each(function(j, e2) {
                var id = $(this).data('id')==undefined?'0':$(this).data('id');
				var widget_name =$(this).data('name')==undefined?"":$(this).data('name');
				layoutItems[j] = "{\"widget_id\":\""+id+"\",\"widget_name\":\""+widget_name+"\"}";
            });
			layouts[i] = "{\"type\":\""+type+"\",\"widgets\":["+layoutItems.join(",")+"]}";
        });
		$.post(window.location.href,[map('bgId',$('#phoneArea').data('bgid')),map('layouts',"["+layouts.join(",")+"]")],function(data){
				updateAlert(data.info);
				setTimeout(function(){
					$("#saveLayout").removeClass("loading").text("保存");
					$('#top-alert').find('button').click();
					window.location.href=data.url;
				},1500);
			})
	})
});