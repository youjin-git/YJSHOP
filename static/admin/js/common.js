$(".check-all").click(function(){
			$(".ids").prop("checked", this.checked);
});
$('#search').find('.time').click(function(){
				var _this = $(this).next();
			
				if(_this.css('display')=='block'){
						_this.hide();
				}else{
						_this.show();
				}
			
})
$('#side-menu').find('span').click(function(){
		if($(this).next().css('display')=='none'){
			   $(this).next().slideDown();
			   
		}else{
			   $(this).next().slideUp();
		}
});
$(".ids").click(function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
});

$('.nav-tabs').children('li').children('a').click(function(){
	
})
$('.nav-tabs').children('li').children('a').click(function(){
	var targ = $(this).attr('href');
	window.location.hash = "#"+targ.substr(4);
})
$('.ajax-get').click(function(){
		var target,_this=$(this);
		if($(this).hasClass('confirm')){
		    if(!confirm('确认要执行该操作吗?')){
                return false;
            }
		}
		if((target = _this.attr('href'))||(target = $(this).attr('url'))){
				$.get(target,{},function(data){
						if(data.code==1){
							if (data.msg){
		                   	  	updateAlert(data.msg + ' 页面即将自动跳转~','alert-success');
		                    }else{
		                      	updateAlert(data.msg ,'alert-success');
		                    }
						}else{
                  			 updateAlert(data.msg,'alert-danger');
		 		 		}
						setTimeout(function(){
                    	_this.removeClass('disabled').prop('disabled',false);
                        if (data.url=='javascript:history.back(-1);'&&data.url!='') {
                          	 $('.alert').find('button').click();
                        }else{
                            location.href=data.url;
                        }
                   	 },2000);
					
				})
				
		}
	return false;
})
$('.ajax-post').click(function(){

		var target_form = $(this).attr('target-form'),target,_this=$(this);
		var form = $('.'+target_form);
		if( (_this.attr('type')=='submit') || (target = _this.attr('href')) || (target = _this.attr('url'))){
		if(form.get(0).nodeName=='FORM'){
				query = form.serialize();
				if($(this).attr('url') !== undefined){
                	target = $(this).attr('url');
                }else{
                	target = form.get(0).action;
                }
		}else{
			  if (_this.hasClass('confirm')){
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
			  query = form.serialize();
			  
		}
		
		_this.addClass('disabled').attr('autocomplete','off').prop('disabled',true);
	
		$.post(target,query,function(data){
				if (data.code==1) {
						 if (data.msg){
		                   	  	updateAlert(data.msg + ' 页面即将自动跳转~','alert-success');
		                    }else{
		                      	updateAlert(data.msg ,'success');
		                    }
		 		 }else{
                   	 updateAlert(data.msg,'danger');
		 		 }
				   setTimeout(function(){
                    	_this.removeClass('disabled').prop('disabled',false);
                       
                        if (data.url=='javascript:history.back(-1);'||data.url=='') {
                          	 $('.alert').find('button').click();
                        }else{
                            location.href=data.url;
                        }
                    },2000);
			
			
		})
		 
		}
		return false;
})
var top_alert = $('.alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});
window.updateAlert = function (msg,status,url,time) {
	
  		msg = msg ||'yjshop';
  		if(url)msg=msg+' 页面即将自动跳转 ~';
  		top_alert.find('strong').text(msg);
  		status = status||'success';
  		time = time || '2000';
  		top_alert.removeClass('alert-error alert-warn alert-info alert-success').addClass('alert-'+status);
  		top_alert.addClass('block').slideDown(200);
		setTimeout(function(){
				
				if(url)location.href=url;
				top_alert.removeClass('block').slideUp(200);
		},time)
		
};
