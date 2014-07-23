var widget_name = parent.currentTargetParam.widget_name;
var url = parent.currentTargetParam.url;
var htmlUrl = parent.currentTargetParam.htmlUrl;
var saveUrl = parent.currentTargetParam.saveUrl;
var templateId = parent.currentTargetParam.templateId;
var DiyPageId = parent.DiyPageId;
var WeiPHPMid = parent.WeiPHPMid;
$('#confirm,#editwidgetConfirm').click(function(){
	var formData = $('#baseSettingForm').serialize()+'&'+$('#editwidgetForm').serialize();;
	
	parent.currentTarget.attr('data-param',encodeURIComponent(formData));
	parent.currentTarget.attr('data-name',widget_name);
	parent.currentTarget.attr('data-id',templateId);
	parent.currentTarget.attr('data-set',url);
	parent.currentTarget.attr('data-html',htmlUrl);
	parent.currentTarget.attr('data-save',htmlUrl);
	parent.currentTarget.attr('data-id',templateId);

	formData += '&page_id='+DiyPageId+'&widget_id='+templateId+'&widget_name='+widget_name;
	$.post(url,formData,function(templateHtml){
		parent.currentTarget.addClass('function_edit');
		parent.currentTarget.find('.item_content').html(templateHtml);
		$('.dialog_layer', parent.document).hide();
		$('#setDialog', parent.document).hide();
		$('body', parent.document).removeClass('no-scroll');
		$('#paramIframe', parent.document).attr('src','');			
	});
	
});
//切换编辑html
$('.setting').click(function(){
	$(' #baseSettingForm').show();
	$(' #editwidgetForm').hide();
	$(' #editHtmlForm').hide();
	$(this).addClass('cur').siblings().removeClass('cur');
})
$('.editwidget').click(function(){
	$(' #baseSettingForm').hide();
	$(' #editwidgetForm').show();
	$(' #editHtmlForm').hide();
	$(this).addClass('cur').siblings().removeClass('cur');
})				
$('.edithtml').click(function(){
	$(' #baseSettingForm').hide();
	$(' #editwidgetForm').hide();
	$(' #editHtmlForm').show();
	var templateName = $('select[name="template"]').val();
	$.post(htmlUrl,{'widget_name':widget_name,'template':templateName,'widget_id':templateId},function(htmlCode){
		$('#htmlTextarea').val(htmlCode);
		})
	
})

$('#editHtmlForm #saveHtmlBtn').click(function(){
	var templateName = $('select[name="template"]').val();
	var templateCode = $('#editHtmlForm #htmlTextarea').val();
	$('#custom_template').val(templateId);
	$('#template').val(templateId);
	$.post(saveUrl,{'widget_name':widget_name,'template':templateName,'widget_id':templateId,'code':templateCode},function(data){
			updateAlert(data.info);
			setTimeout(function(){
				$('#top-alert').find('button').click();
			},1500);
			$(' #baseSettingForm').show();
			$(' #editHtmlForm').hide();
		})
	
})

$('#addWidgetMoreLink').click(function(){
	var $clone = $('.form-col-more').eq(0).clone();
	$clone.insertAfter($(this).parents('.form-col-more'));
})	
$('.preview_btn').click(function(){
	var formData = $('#baseSettingForm').serialize()+'&'+$('#editwidgetForm').serialize();

	parent.currentTarget.attr('data-name',widget_name);
	parent.currentTarget.attr('data-id',templateId);
	parent.currentTarget.attr('data-set',url);

	formData += '&preview=1&page_id='+DiyPageId+'&widget_id='+templateId+'&widget_name='+widget_name;
	$.post(url,formData,function(templateHtml){
		//$('#widget_preview').html(templateHtml);
		var prevHtml = $('<div class="diy_dialog" id="widgetPreview"><div class="content"><div class="layout_item">'+templateHtml+'</div></div></div>');
		var layer = $('<div class="body_layer"></div>');
		var closeHtml = $('<div class="close_widget_preview" title="退出预览"></div>');
		$('body', parent.document).append(layer);
		$('body', parent.document).append(prevHtml);		
		$('body', parent.document).append(closeHtml);
		closeHtml.click(function(){
			prevHtml.remove();
			layer.remove();
			closeHtml.remove();
			})		
	});
						
	
})	