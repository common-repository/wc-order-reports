(function( $ ) {
	/**
	 * This enables you to define handlers, for when the DOM is ready:
	 * $(function() {  });
	 * When the window is loaded:
	 * $( window ).load(function() { });
	 */
})( jQuery );
(function( $ ){	
	jQuery(document).ready(function(){
		/* pixels page */
		jQuery("#save-wcor-account-settings").on("submit", function( event ){
			event.preventDefault();
			document.getElementById("save-wcor-account-settings").disabled = true;
			/*var data = {
        action : 'wcor_check_privecy_policy',
       	data : jQuery(this).serialize()
      };*/
      var data = jQuery("#save-wcor-account-settings").serializeArray();
      wcorder_helper.pmw_ajax_call(data);
		});
		jQuery("#wcor_accept_privecy_policy").on("click", function () {
      event.preventDefault();
      wcorder_helper.close_privacy_popup();
      if(document.getElementById("ch_is_theme_plugin_list").checked){
      	document.getElementById("is_theme_plugin_list").value = 1;
      }else {
		    document.getElementById("is_theme_plugin_list").value = 0;
		  }
      /*change action value*/
      var action_els=document.getElementsByName("action");
			for (var i=0;i<action_els.length;i++) {
				action_els[i].value = "wcor_account_settings_save";
			}
			/*end */
      var data = jQuery("#save-wcor-account-settings").serializeArray();
      wcorder_helper.pmw_ajax_call(data);
    });

    jQuery("#pmw-pixels-licensekey").on("submit", function( event ){
			event.preventDefault();
			var data = jQuery("#pmw-pixels-licensekey").serializeArray();
      wcorder_helper.pmw_ajax_call(data);
		});
		
	});
})( jQuery );

var  chart_ids = {};
/**
 * start js helper
 */
var wcorder_helper = {
	pmw_loader:function(isShow){
		if (isShow){
	    jQuery("#pmw_loader").addClass("is_loading");
	  }else{
	  	jQuery("#pmw_loader").removeClass("is_loading");
	  }
	},
	add_message:function(type, title, msg, is_close = true){
	  let pmw_popup_box = document.getElementById('pmw_form_message');
	  pmw_popup_box.classList.add("active");
	  title = (title)?"<h4>"+title+"</h4>":"";
	  if(type == "success"){
	    document.getElementById('pmw_form_message').innerHTML ="<div class='toaster-box tvc-alert-success'>"+ title +"<p>"+ msg +"</p></div>";
	  }else if(type == "error"){
	    document.getElementById('pmw_form_message').innerHTML ="<div class='toaster-box tvc-alert-error'>"+ title +"<p>"+ msg+"</p></div>";
	  }else if(type == "warning"){
	    document.getElementById('pmw_form_message').innerHTML ="<div class='toaster-box tvc-alert-warning'>"+ title +"<p>"+ msg+"</p></div>";
	  }
	  if(is_close){
	    pmw_time_out = setTimeout(function(){  
	    	pmw_popup_box.classList.remove("active");        
	    }, 4000);
	  } 
	},
	pmw_ajax_call:function(form_data) {
		var f_data = {};
		if(form_data.length >0 ){
	    for (var i = 0; i < form_data.length; i++){
	       f_data[form_data[i]['name']] = form_data[i]['value'];
	    }
	  }else{
	  	return;
	  }
		var this_var = this;
		jQuery.ajax({
      type: "POST",
      dataType: "json",
      url: wor_ajax_url,
      data: f_data,
      beforeSend: function(){
        this_var.pmw_loader(true);
      },
      success: function (response) {
      	console.log(f_data.action);
      	if( f_data.action == "wcor_check_privecy_policy" && !response.hasOwnProperty('message')){
      		if (response.error === true ){
      			wcorder_helper.show_privacy_popup();
      		}else{
      			/*change action value*/
      			document.getElementById("pixels_save_action").value = "wcor_account_settings_save";
						/*end */
			      var data = jQuery("#save-wcor-account-settings").serializeArray();
			      wcorder_helper.pmw_ajax_call(data);			      
      		}
      		return false;
      	}
      	if( f_data.action == "wcor_account_settings_save" || f_data.action == "wcor_check_privecy_policy"){ //remove disabled save button
      		document.getElementById("pixels_save").disabled = false;
      		/*change action value*/
			      document.getElementById("pixels_save_action").value = "wcor_check_privecy_policy";
					/*end */
      	}
      	if (response.error === false && response.hasOwnProperty('message') && response.message != "" ) {         
	        this_var.add_message("success", "Success",  response.message);
	        //refresh page after license keu update
	        if(f_data.action == "wcor_license_key_save"){
		      	location.reload();
		      }
	      }else if(response.hasOwnProperty('message') && response.message != ""){
	      	this_var.add_message("error", "Error", response.message);
	      }else{
	      	this_var.add_message("error", "Error", "while save data.");
	      }
      	this_var.pmw_loader(false);
      }
    });
	},
	show_privacy_popup:function(){
		let body = document.getElementsByClassName('wp-admin');
		let popup = document.getElementById('pmw_privacy_popup');
		body[0].classList.add("modal-open");
		popup.classList.add("show");
		body[0].insertAdjacentHTML('afterend', '<div id="modal-backdrop" class="modal-backdrop fade show"></div>');
	},
	close_privacy_popup:function(){
		let body = document.getElementsByClassName('wp-admin');
		let popup = document.getElementById('pmw_privacy_popup');
		body[0].classList.remove("modal-open");
		popup.classList.remove("show");
		let modal_backdrop = document.getElementById("modal-backdrop");
		if(modal_backdrop != null){
			modal_backdrop.remove();
		}
		this.pmw_loader(false);
		//remove disabled save button
		document.getElementById("pixels_save").disabled = false;
	},
 	wor_alert:function(msg_type=null, msg_subject=null, msg, auto_close=false, wor_time=7000){
		document.getElementById('wor_msg_title').innerHTML ="";
		document.getElementById('wor_msg_content').innerHTML ="";
		document.getElementById('wor_msg_icon').innerHTML ="";

		if(msg != ""){
			let wor_popup_box = document.getElementById('wor_popup_box');
			wor_popup_box.classList.remove("wor_popup_box_close");
			wor_popup_box.classList.add("wor_popup_box");

	  	//wor_popup_box.style.display = "block";
	  	document.getElementById('wor_msg_title').innerHTML =this.wor_subject_title(msg_type, msg_subject);
			document.getElementById('wor_msg_content').innerHTML =msg;
			if(msg_type=="success"){
				document.getElementById('wor_msg_icon').innerHTML ='<i class="fas fa-check-circle fa-3x tvc-success"></i>';
			}else{
				document.getElementById('wor_msg_icon').innerHTML ='<i class="fas fa-exclamation-circle fa-3x"></i>';
			}
			if(auto_close == true){
				setTimeout(function(){  //wor_popup_box.style.display = "none";				
					wor_popup_box.classList.add("wor_popup_box_close");
					wor_popup_box.classList.remove("wor_popup_box");				
				}
				, wor_time);
			}
		}
	},
	wor_subject_title:function(msg_type=null, msg_subject=null){
		if(msg_subject == null || msg_subject ==""){
			if(msg_type=="success" ){
				return '<span class="tvc-success">Success!!</span>';
			}else{
				return '<span class="tvc-error">Oops!</span>';
			}
		}else{
			if(msg_type=="success" ){
				return '<span class="tvc-success">'+msg_subject+'</span>';
			}else{
				return '<span>'+msg_subject+'</span>';
			}
		}		
	},
	wor_close_msg:function(){
		let wor_popup_box = document.getElementById('wor_popup_box');
		wor_popup_box.classList.add("wor_popup_box_close");
		wor_popup_box.classList.remove("wor_popup_box");
		//wor_popup_box.style.display = "none";
	},
	get_sales_report_analysis:function(post_data){
		//console.log(post_data);
		this.cleare_sales_reports('sales_analysis');
		this.add_loader_for_sales_reports('sales_analysis');
		this.sales_reports_call_data(post_data,'sales_analysis');
	},
	get_sales_data_analysis:function(post_data){
		//console.log(post_data);
		this.cleare_sales_reports('sales_data_analysis');
		this.add_loader_for_sales_reports('sales_data_analysis');
		this.sales_reports_call_data(post_data,'sales_data_analysis');
	},
	sales_reports_call_data:function(post_data, page){
		var v_this = this;
		jQuery.ajax({
      type: "POST",
      dataType: "json",
      url: wor_ajax_url,
      data: post_data,
      success: function (response) {
      	console.log(response);
      	if(response.error == false){
      		if(Object.keys(response.data).length > 0 && page == 'sales_data_analysis'){
      			v_this.set_sales_data_analysis_value(response.data, post_data);
      		}else if(Object.keys(response.data).length > 0 && page == 'sales_analysis'){
      			v_this.set_sales_analytics_value(response.data, post_data);
      		}
      	}else if(response.error == true && response.errors != undefined){
	        const errors = response.errors[0];
	        //v_this.wor_alert("error","Error",errors);
	      }else{
	      		//v_this.wor_alert("error","Error","Sales report data not fetched");
	      }
        v_this.remove_loader_for_analytics_reports(page);
      }
    });
	},
	set_sales_analytics_value:function(data, post_data){
		var v_this = this;		
		var basic_data = data.summury;
		//console.log(data);
		var currency_code = data.currency;
		var plugin_url = post_data.plugin_url;
		var global_chart_json = "";
		if(post_data.hasOwnProperty('global_chart_json')){
			global_chart_json = post_data.global_chart_json;
			//console.log(global_chart_json);
		}
		var reports_typs = {
			basec_data:{
				is_free:true
			},product_performance_report:{
				is_free:false
			},medium_performance_report:{
				is_free:false
			},conversion_funnel:{
				is_free:false
			},checkout_funnel:{
				is_free:false
			}
		};
		var paln_type = 'free';
		if(post_data.plan_id != 1){
			paln_type='paid';
		}
		if(Object.keys(global_chart_json).length > 0){
			var temp_val =""; var temp_div_id = "";
			jQuery.each(global_chart_json, function (propKey, propValue) {	
				/**
					* set fields value
					*/			
				if(basic_data.hasOwnProperty(propValue['id'])){
					temp_val = basic_data[propValue['id']];
					temp_div_id = "#s1_"+propValue['id']+" > .sales-smry-value";
					v_this.display_field_val(temp_div_id, propValue, temp_val, propValue['type'], currency_code);
				}else{
					temp_div_id = "#s1_"+propValue['id']+" > .sales-smry-value";
					v_this.display_field_val(temp_div_id, propValue, 0, propValue['type'], currency_code);
				}
				/*if(basic_data.hasOwnProperty('compare_'+propValue['id'])){
					temp_val = basic_data['compare_'+propValue['id']];
					temp_div_id = "#s1_"+propValue['id']+" > .sales-smry-compare-val";
					v_this.display_field_val(temp_div_id, propValue, temp_val, 'rate', currency_code, plugin_url);

					//$("#s1_"+propValue['id']+" > .dash-smry-value").html(temp_val);
				}*/

				/**
					* drow_chart all chart
					*/				
				if(propValue['chart_info']!= undefined && propValue['is_chart'] != undefined ){
					var chart_info = propValue['chart_info'];
					v_this.drow_chart(chart_info, data);						
				}		
			});
		}
		/**
			* Display table
			*/
		/*if(data.hasOwnProperty('product_performance_report') && ( reports_typs.product_performance_report.is_free || paln_type == 'paid')){
			var p_p_r = data.product_performance_report.products;
			var table_row = '';
			if(p_p_r != undefined && Object.keys(p_p_r).length > 0){
				jQuery.each(p_p_r, function (propKey, propValue) {
					table_row = '';
					table_row += '<tr><td class="prdnm-cell">'+propValue['productName']+'</td>';
					table_row += '<td>'+propValue['productDetailViews']+'</td>';
					jQuery("#product_performance_report table tbody").append(table_row);
				})
			}else{
				jQuery("#product_performance_report table tbody").append("<tr><td>Data not available</td></tr>");
			}
		}*/
	},
	set_sales_data_analysis_value:function(data, post_data){
		var v_this = this;		
		var basic_data = data.summury;
		//console.log(basic_data);
		var currency_code = data.currency;
		var plugin_url = post_data.plugin_url;
		var s_1_div_id ={
			'total_sale':{
				'id':'total_sale',
				'type':'currency'
			},'net_sale':{
				'id':'net_sale',
				'type':'currency'
			},'total_orders':{
				'id':'total_orders',
				'type':'number'
			},'average_order_value':{
				'id':'average_order_value',
				'type':'currency'
			},'refund_order':{
				'id':'refund_order',
				'type':'number'
			},'refund_order_value':{
				'id':'refund_order_value',
				'type':'currency'
			},'discount_amount':{
				'id':'discount_amount',
				'type':'currency'
			},'total_tax':{
				'id':'total_tax',
				'type':'currency'
			},'order_tax':{
				'id':'order_tax',
				'type':'currency'
			},'shipping_tax':{
				'id':'shipping_tax',
				'type':'currency'
			},'shipping':{
				'id':'shipping',
				'type':'currency'
			},'wc_on_hold':{
				'id':'wc_on_hold',
				'type':'number'
			},'wc_processing':{
				'id':'wc_processing',
				'type':'number'
			},'wc_cancelled':{
				'id':'wc_cancelled',
				'type':'number'
			},'wc_completed':{
				'id':'wc_completed',
				'type':'number'
			}
		};
		var reports_typs = {
			basec_data:{
				is_free:true
			},product_performance_report:{
				is_free:false
			},medium_performance_report:{
				is_free:false
			},conversion_funnel:{
				is_free:false
			},checkout_funnel:{
				is_free:false
			}
		};
		var paln_type = 'free';
		if(post_data.plan_id != 1){
			paln_type='paid';
		}
		if(Object.keys(s_1_div_id).length > 0){
			var temp_val =""; var temp_div_id = "";
			jQuery.each(s_1_div_id, function (propKey, propValue) {				
				if(basic_data.hasOwnProperty(propValue['id'])){
					temp_val = basic_data[propValue['id']];
					temp_div_id = "#s1_"+propValue['id']+" > .sales-smry-value";
					v_this.display_field_val(temp_div_id, propValue, temp_val, propValue['type'], currency_code);
				}else{
					temp_div_id = "#s1_"+propValue['id']+" > .sales-smry-value";
					v_this.display_field_val(temp_div_id, propValue, 0, propValue['type'], currency_code);
				}
				/*if(basic_data.hasOwnProperty('compare_'+propValue['id'])){
					temp_val = basic_data['compare_'+propValue['id']];
					temp_div_id = "#s1_"+propValue['id']+" > .sales-smry-compare-val";
					v_this.display_field_val(temp_div_id, propValue, temp_val, 'rate', currency_code, plugin_url);

					//$("#s1_"+propValue['id']+" > .dash-smry-value").html(temp_val);
				}	*/			
			});
		}

		if(data.hasOwnProperty('cart_abandoned') ){
			var cart_items = data.cart_abandoned;
			//console.log(cart_items);
			var table_row = '';
			if(cart_items != undefined && Object.keys(cart_items).length > 0){
				jQuery.each(cart_items, function (propKey, item) {
					var customer ="";
					var customer_email ="";
					var user_data = JSON.parse(item['user_data']);
					var order_user_data ="";
					if(user_data.hasOwnProperty('order_user_data')){
						order_user_data = user_data.order_user_data;
					}
					if(user_data.hasOwnProperty('user_data')){
						user_data = user_data.user_data;
					}
					//console.log(user_data);
					if(item.hasOwnProperty('user_id') && item['user_id'] >0){						
						customer = user_data.user_login;
						customer_email = user_data.user_email;
					}else if(item.hasOwnProperty('ip_address') && item['ip_address'] != ""){
						ip_address = item['ip_address'];
						//console.log(order_user_data);
						if(order_user_data.hasOwnProperty('billing_first_name') && order_user_data.billing_first_name != ""){
							customer+=order_user_data.billing_first_name;
						}
						if(order_user_data.hasOwnProperty('billing_last_name') && order_user_data.billing_last_name != ""){
							customer+=" "+order_user_data.billing_last_name;
						}
						if(order_user_data.hasOwnProperty('billing_email') && order_user_data.billing_email != ""){
							customer_email+=order_user_data.billing_email;
						}

					}
					var currency = v_this.get_currency_symbols(item['currency']);
					table_row = '';
					table_row += '<div class="row-body clearfix"><div class="column sm-column">'+item['id']+'</div>';
					table_row += '<div class="column lg-column">'+customer_email+'</div>';
					table_row += '<div class="column">'+customer+'</div>';
					table_row += '<div class="column">'+item['user_type']+'</div>';
					table_row += '<div class="column lg-column">'+item['created_at']+'</div>';
					table_row += '<div class="column">'+item['week_day']+'</div>';
					table_row += '<div class="column">'+item['order_quantity']+'</div>';
					table_row += '<div class="column">'+currency+item['sub_total']+'</div></div>';
					jQuery(".cart_abandoned_data_sec .esc-table-body").append(table_row);
				})
			}else{
				jQuery(".cart_abandoned_data_sec .esc-table-body").append("<div class='row-body clearfix'><div class='column'>Data not available</div></div>");
			}
		}
		//order status
		if(data.hasOwnProperty('order_status') ){
			var cart_items = data.order_status;
			//console.log(cart_items);
			var table_row = '';
			if(cart_items != undefined && Object.keys(cart_items).length > 0){
				jQuery.each(cart_items, function (propKey, item) {
					
					var currency = v_this.get_currency_symbols(item['currency']);
					var refund_amount = (item['refund_amount'] != null)?item['refund_amount']:"0";
					var discount_amount = (item['discount_amount'] != null)?item['discount_amount']:"0";
					table_row = '';
					table_row += '<div class="row-body clearfix"><div class="column">'+propKey.replace("wc_","")+'</div>';
					table_row += '<div class="column">'+item['order_total']+'</div>';
					table_row += '<div class="column">'+item['line_subtotal']+'</div>';
					table_row += '<div class="column">'+item['total_orders']+'</div>';
					table_row += '<div class="column">'+item['line_qty']+'</div>';
					table_row += '<div class="column">'+discount_amount+'</div>';
					table_row += '<div class="column">'+refund_amount+'</div>';
					table_row += '<div class="column">'+item['order_tax']+'</div>';
					table_row += '<div class="column">'+item['order_shipping_tax']+'</div>';
					table_row += '<div class="column">'+item['shipping']+'</div></div>';
					
					jQuery(".order_performance_data_sec .esc-table-body").append(table_row);
				})
			}else{
				jQuery(".order_performance_data_sec .esc-table-body").append("<div class='row-body clearfix'><div class='column'>Data not available</div></div>");
			}
		}

	},
	display_field_val:function(div_id, field, field_val, field_type, currency_code, plugin_url){
		//console.log(field_val+"-"+div_id);
		if(field_type == "currency"){
			var currency = this.get_currency_symbols(currency_code);
			jQuery(div_id).html(currency +''+field_val);
		}else if(field_type == "rate"){
			field_val = parseFloat(field_val).toFixed(2);
			var img = "";
			if(plugin_url != "" && plugin_url != undefined){
				img = '<img src="'+plugin_url+'/admin/images/red-down.png">';
				if(field_val >0){
					img = '<img src="'+plugin_url+'/admin/images/green-up.png">';
				}
			}
			jQuery(div_id).html(img+field_val+'%');
		}else {
			jQuery(div_id).html(field_val);
		}

	},
	drow_chart:function(chart_info, alldata, d_backgroundColor ='#0080F7'){		
		var chart_id = chart_info.chart_id;
		var chart_data = alldata.date;
		if(chart_id == "order_status_chart"){
			chart_data = alldata.summury.order_status;
		}
		
		var chart_metrics = chart_info.chart_metrics;
		var d_label = chart_info.chart_title;
		var chart_type = chart_info.chart_type;
		var chart_tension = "0";
		if(chart_info.hasOwnProperty('tension')){
			chart_tension = chart_info['tension'];
		}
		if(chart_info.hasOwnProperty('backgroundColor')){
			d_backgroundColor = chart_info['backgroundColor'];
		}
		
		var ctx = document.getElementById(chart_id).getContext('2d');		
		var labels = [];
		var chart_val = [];

		var t_date = "";
		var chart_datasets = [];
		var t_label = "";
		var t_val = "";
		var t_borderColor="#9AD0F5";
		var t_html="";
		jQuery.each(chart_metrics, function (m_key, m_value) {
			chart_val = [];
			labels = [];
			t_label = m_value['label'];
			if(m_value.hasOwnProperty('borderColor')){
				t_borderColor = m_value['borderColor'];
			}
			if(m_value.hasOwnProperty('backgroundColor')){
				d_backgroundColor = m_value['backgroundColor'];
			}
			jQuery.each(chart_data, function (key, value) {
				t_date = value[m_value['dimensions']];
				t_val = ((value[m_value['metrics']]!=null)?value[m_value['metrics']]:0);
				if(chart_id == "order_status_chart"){
					t_date = key;
					t_html=t_html+key+":"+t_val+" ";
				}				
				//console.log(value[m_value['metrics']]);
				//console.log(value);
			  labels.push(t_date.toString());
			  chart_val.push(((value[m_value['metrics']]!=null)?value[m_value['metrics']]:0));
			});
			
			chart_datasets.push({label: t_label, data:chart_val, borderColor: t_borderColor,
		      backgroundColor: d_backgroundColor,tension: chart_tension})
		});
		if(chart_id == "total_users_chart"){	
			console.log(chart_datasets);
		}
		if(chart_id == "order_status_chart"){
			temp_div_id = "#s1_order_status > .sales-smry-value";
			jQuery(temp_div_id).html(t_html);
		}
		const data = {
		  labels: labels,
		  datasets: chart_datasets
		};
		const config = {
		  type: chart_type,
		  data: data,
		  options: {
		    responsive: true,
		    plugins: {
		      legend: {
		        position: 'top',
		      },
		      title: {
		        display: true,
		        text: d_label
		      }
		    }
		  },
		};
		chart_ids[chart_id] = new Chart(ctx,config);
	},
	/*drow_chart_t:function(chart_id, chart_type, alldata){
		var chart_data = alldata.date;
		if(chart_id == "total_sale_chart"){
			var ctx = document.getElementById(chart_id).getContext('2d');
			const DATA_COUNT = 12;
			const labels = [];
			const net_sales = [];
			const total_sale = []; 
			var t_date = "";
			jQuery.each(chart_data, function (key, value) {
				t_date = value['order_date'];
			  labels.push(t_date.toString());
			  net_sales.push(((value['line_subtotal']!=null)?value['line_subtotal']:0));
			  total_sale.push(((value['order_total']!=null)?value['order_total']:0));
			});
			//const datapoints = [0, 20, 20, 60, 60, 120, 45, 180, 120, 125, 105, 110, 170];
			const data = {
			  labels: labels,
			  datasets: [
			    {
			      label: 'Total sales',
			      data: total_sale,
			      borderColor: '#878743',
			      fill: false,
			      cubicInterpolationMode: 'monotone',
			      tension: 0.4
			    }, {
			      label: 'Net Sales',
			      data: net_sales,
			      borderColor: '#8BBFEC',
			      fill: false,
			      tension: 0.4
			    }
			  ]
			};
			const config = {
			  type: 'line',
			  data: data,
			  options: {
			    responsive: true,
			    plugins: {
			      title: {
			        display: true,
			        text: 'Total sales - Net Sales'
			      },
			    },
			    interaction: {
			      intersect: false,
			    },
			    scales: {
			      x: {
			        display: true,
			        title: {
			          display: true
			        }
			      },
			      y: {
			        display: true,
			        title: {
			          display: true,
			          text: 'Value'
			        },
			        suggestedMin: 0,
			        suggestedMax: 200
			      }
			    }
			  },
			};
			chart_ids[chart_id] = new Chart(ctx,config);
			//total_sale_chart
		}

	},
	add_genrale_pie_chart:function(chart_id, alldata, field_key,  d_label, is_labels_as_key =false){
		var chart_data = alldata;
		var ctx = document.getElementById(chart_id).getContext('2d');
			
		const labels = [];
		const chart_val = [];
		var t_labels = "";
		var d_backgroundColors = ['#FF6384','#22CFCF','#0ea50b','#FF9F40','#FFCD56']
		jQuery.each(chart_data, function (key, value) {
			if(is_labels_as_key){
				t_labels =key;
			}else{
				t_labels = value['order_date'];
			}				
		  labels.push(t_labels.toString());
		  //chart_val.push(value[field_key]);
		  chart_val.push(((value[field_key]!=null)?value[field_key]:0));
		});
		const data = {
			  labels: labels,
			  datasets: [
			    {
			      label: d_label,
			      data: chart_val,
			      backgroundColor: d_backgroundColors,
			    }
			  ]
			};
			const config = {
			  type: 'pie',
			  data: data,
			  options: {
			    responsive: true,
			    plugins: {
			      legend: {
			        position: 'top',
			      },
			      title: {
			        display: true,
			        text: d_label
			      }
			    }
			  },
			};
			chart_ids[chart_id] = new Chart(ctx,config);
	},*/
	remove_loader_for_analytics_reports:function(page){
		var reg_section = this.get_sales_reports_section(page);
		if(Object.keys(reg_section).length > 0){
			jQuery.each(reg_section, function (propKey, propValue) {
				if(propValue.hasOwnProperty('main-class') && propValue.hasOwnProperty('loading-type')){
					if(propValue['loading-type'] == 'bgcolor'){
						//$("."+propValue['main-class']).addClass("is_loading");
						if(Object.keys(propValue['ajax_fields']).length > 0){
							jQuery.each(propValue['ajax_fields'], function (propKey, propValue) {
									jQuery("."+propValue['class']).removeClass("loading-bg-effect");
							});
						}
					}else if(propValue['loading-type'] == 'gif'){
						jQuery("."+propValue['main-class']).removeClass("is_loading");
					}

				}
			});
			
		}
	},
	cleare_sales_reports:function(page){
		var v_this = this;
		
		
		if(page=='sales_data_analysis'){
			jQuery(".cart_abandoned_data_sec .esc-table-body").html("");
			jQuery(".order_performance_data_sec .esc-table-body").html("");
			
		}else if(page == 'sales_analysis'){
			if(Object.keys(chart_ids).length > 0){
				jQuery.each(chart_ids, function (propKey, propValue) {
					var canvas = document.getElementById(propKey);
					if( canvas != null){
						var is_blank = v_this.is_canvas_blank(canvas);
				    if(!is_blank){
				    	chart_ids[propKey].destroy();		    	
				    }
				  }
				});			
			}
		}
		/*canvas = document.getElementById('ecomcheckoutfunchart');
	  if(canvas != null){
	    var is_blank = this.is_canvas_blank(canvas);
	    if(!is_blank){
	    	checkout_bar_chart.destroy();
	    	//const canvas = document.getElementById('ecomfunchart');
		  		//canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
	    }
	  }*/
	},
	add_loader_for_sales_reports:function(page){
		var reg_section = this.get_sales_reports_section(page);
		if(Object.keys(reg_section).length > 0){
			jQuery.each(reg_section, function (propKey, propValue) {
				if(propValue.hasOwnProperty('main-class') && propValue.hasOwnProperty('loading-type')){
					if(propValue['loading-type'] == 'bgcolor'){
						//$("."+propValue['main-class']).addClass("is_loading");
						if(Object.keys(propValue['ajax_fields']).length > 0){
							jQuery.each(propValue['ajax_fields'], function (propKey, propValue) {
									jQuery("."+propValue['class']).addClass("loading-bg-effect");
							});
						}
					}else if(propValue['loading-type'] == 'gif'){
						jQuery("."+propValue['main-class']).addClass("is_loading");
					}

				}
			});			
		}
	},
	get_sales_reports_section:function(page){
		if(page=='sales_data_analysis'){
			return {
				'dashboard_summary':{
					'loading-type':'bgcolor',
					'main-class':'wor-sales-rep-sec-1',
					'sub-clsass':'product-card',
					'ajax_fields':{
						'field_1':{
							'class':'sales-smry-title'
						},'field_2':{
							'class':'sales-smry-value'
						}
					}
				},'product_performance_report':{
					'loading-type':'gif',
					'main-class':'product_performance_report',
				},'esc_table_body':{
					'loading-type':'gif',
					'main-class':'esc-table-body',
				}
				
			};
		}else if(page == 'sales_analysis'){
			return {
				'dashboard_summary':{
					'loading-type':'bgcolor',
					'main-class':'wor-sales-rep-sec-1',
					'sub-clsass':'product-card',
					'ajax_fields':{
						'field_1':{
							'class':'sales-smry-title'
						},'field_2':{
							'class':'sales-smry-value'
						}
					}
				},'total_sale_chart':{
					'loading-type':'gif',
					'main-class':'total-sale-chart',
				},'total_orders_chart':{
					'loading-type':'gif',
					'main-class':'total-orders-chart',
				},'average_order_value_chart':{
					'loading-type':'gif',
					'main-class':'average-order-value-chart',
				},'refund_order_chart':{
					'loading-type':'gif',
					'main-class':'refund-order-chart',
				},'refund_order_value_chart':{
					'loading-type':'gif',
					'main-class':'refund-order-value-chart',
				},'discount_amount_chart':{
					'loading-type':'gif',
					'main-class':'discount-amount-chart',
				},'total_tax_chart':{
					'loading-type':'gif',
					'main-class':'total-tax-chart',
				},'order_tax_chart':{
					'loading-type':'gif',
					'main-class':'order-tax-chart',
				},'shipping_tax_chart':{
					'loading-type':'gif',
					'main-class':'shipping-tax-chart',
				},'shipping_chart':{
					'loading-type':'gif',
					'main-class':'shipping-chart',
				},'total_users_chart':{
					'loading-type':'gif',
					'main-class':'total-users-chart',
				},'order_status_chart':{
					'loading-type':'gif',
					'main-class':'order-status-chart',
				}
			};
		}
		
	},get_currency_symbols:function(code){
		var currency_symbols = {
		    'USD': '$', // US Dollar
		    'EUR': '€', // Euro
		    'CRC': '₡', // Costa Rican Colón
		    'GBP': '£', // British Pound Sterling
		    'ILS': '₪', // Israeli New Sheqel
		    'INR': '₹', // Indian Rupee
		    'JPY': '¥', // Japanese Yen
		    'KRW': '₩', // South Korean Won
		    'NGN': '₦', // Nigerian Naira
		    'PHP': '₱', // Philippine Peso
		    'PLN': 'zł', // Polish Zloty
		    'PYG': '₲', // Paraguayan Guarani
		    'THB': '฿', // Thai Baht
		    'UAH': '₴', // Ukrainian Hryvnia
		    'VND': '₫', // Vietnamese Dong
		};
		if(currency_symbols[code]!==undefined) {
		  return currency_symbols[code];
		}else{
			return code;
		}
	},is_canvas_blank:function (canvas) {
  	const context = canvas.getContext('2d');
	  const pixelBuffer = new Uint32Array(
	    context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
	  );
  	return !pixelBuffer.some(color => color !== 0);
	}
};//end wor_helper