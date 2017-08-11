var Controller = function() {
	
	
    var controller = {
        self: null,
        initialize: function() {
            self = this;
            this.bindEvents();
            var churchURL = window.localStorage.getItem('churchURL');
			var myaudio;
            if(churchURL=== null)
            {
            	self.renderChooseChurch();
            }
            else
            {
            	self.renderHomeView(); 
        	}
        },

        bindEvents: function() {
        	
        	$('.tab-button').on('click', this.onMenuClick);
        	//Buttons and links rendered in the DOM
        	$('#page #rendered').on('click', '.newsItem', this.onNewsClick);
        	$('#page #rendered').on('click', '.sermon', this.onSermonClick);
        	$('#page #rendered').on('change', '#serviceSelect', this.onRotaSelect);
        	$('#page #rendered').on('change', '#dateSelect', this.onDateSelect);
        	$('#page #rendered').on('click', '#login',this.login);
        	$('#page #rendered').on('click', '#search', this.search);
        	$('#page #rendered').on('click', '#forgottenProcess', this.forgotten);
        	$('#page #rendered').on('click', '#forgotten',this.ForgottenView);
        	$('#firstRun #rendered').on('click', '#churchSave',this.churchSave);
        	$('#page #rendered').on('click', '#logout',this.logout);
        	$('#page #rendered').on('click','.datepicker',this.onBibleClick );
        	
        },
        onMenuClick: function(e) {
            var tab = $(this).data('tab');
           	
            switch(tab)
            {
            	case'#home':self.renderHomeView();break;
            	case'#address':self.renderAddressView();break;
            	case'#media':self.renderMediaView();break;
            	case'#calendar':self.renderCalendarView();break;
            	case'#news':self.renderNewsView();break;
            	case'#smallgroup':self.renderGroupView();break;
            	case'#bible':self.renderBibleView();break;
            	case'#rota':self.renderRotaView(null);break;
            	case'#giving':self.renderGivingView();break;
            	case'#login':self.login();break;
            	case'#search':self.renderSearchView();break;
            	case'#prayer':self.renderPrayerView();break;
            }
        },
        //on first run, users need to select which church they want the app to run with
        //This function saves that selection
        churchSave: function(){
        	var churchURL = $('#churchSelect').val();
        	
        	if(churchURL!='')
        	{
        		//store churchURL in local storage
        		var storage=window.localStorage;
        		storage.setItem('churchURL', churchURL);
        		//download home and giving pages
        		var args={ action: "ca_download_church",url:churchURL };
        		$.getJSON('https://www.churchadminplugin.com/wp-admin/admin-ajax.php',args,downloadChurch);
        	
        		function downloadChurch(data)
        		{
        			storage.setItem('church_name',data.church_name);
        			storage.setItem('home', data.home);
        			storage.setItem('giving', data.giving);
        			storage.setItem('church_id', data.church_id);
        			storage.setItem('groups',data.groups);
        			$('#firstRun').hide();
        			$('#page').show();
        			var html=data.home;
        			$("#page #rendered").html(html);
				}
        		
        		
				
        	}
        	else
        	{
        		this.renderChooseChurch;
        	}
        },
        //On first run or after logout, pull down list of churches subscribed for the app and allow user to choose.
        renderChooseChurch: function(){
        	// Hide whatever page is currently shown.
			$('#page').hide();
			$('#firstRun').show();
        	var args={ action: "ca_choose_church" };
        	var storage=window.localStorage;
        	
        	$.getJSON('https://www.churchadminplugin.com/wp-admin/admin-ajax.php',args,processChurch);
        	function processChurch(data)
        	{	
        		var html='<img src="img/logo.png" class="logo"/><h2>Choose Church</h2>';
        		var churchPicker=data;
           		html= html+'<p><select class="tab-button" data-tab="#church" id="churchSelect">';
        		for(var count = 0; count < churchPicker.length; count++)
        		{
            			var church=churchPicker[count]
            			html = html + '<option value="'+church.url +'">'+church.name +'</option>';
        		}
        		html=html+'</select></p>';
        		html=html+'<p><button class="button" data-tab="#churchSave" id="churchSave">Save Church</button></p>';
        		$("#firstRun #rendered").html(html);
        	}
        	
        
        },
        //forgotten password form
         ForgottenView: function(data){
        	$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#giving-tab-button').addClass('active');
            $('#title').html('Please login');
            var html='<div id="content">';
            var message=data.error;
            if(message === undefined) var message=data.message;
            if(message === undefined) var message=' ';
            html= html +message;
            html= html + '<p>Please enter your username or email to receive a password reset email</p>';
            html=html+'<input id="user_login" type="text" placeholder="Enter Username/Email" autocorrect="off" autocapitalize="none"/>';
            html=html+'<button class="ui-btn" data-tab="#forgottenProcess" id="forgottenProcess">Reset Password</button></div>';
           	$("#page #rendered").html(html);
           	
        },
        //send of forgotten password data
        forgotten: function(){
			$('#firstRun').hide();
        	$('#page').show();
        	var user_login = $('#user_login').val();
        	
        	var data={'error':'Please enter a value'};
        	if(user_login===''){self.ForgottenView(data);}//no value entered
        	else
        	{
        		var args={ action: "ca_forgotten_password", user_login: user_login };
        		var storage=window.localStorage;
        		var churchURL = storage.getItem('churchURL');
        		$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,processForgotten);
        		function processForgotten(data)
        		{
        			
        			if(data.error!=''){self.ForgottenView(data);}//error given
        			else
        			{
        				var html= data.message;
        				$("#page #rendered").html(html);
        			
        			}
        		
        		}
        		
        	}
        
        },
        
        search: function(){
        	
        	
        	var search = $('#s').val();
        	self.renderSearchView(search);
        	
        },
        login: function(){
			
			$('#firstRun').hide();
        	$('#page').show();
        	var username = $('#username').val();
        	var password = $('#password').val();
        	var whereNext = $('#whereNext').val();
        	var token = $.md5(username+ $.md5(password));
        	var storage = window.localStorage;
			storage.setItem('token', token);
			var args={ action: "ca_login", username: username,password: password,UUID:token };
        	var churchURL = storage.getItem('churchURL');
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,processLogin);
        	function processLogin(data)
        	{
        		
        		if(data.login== true) { 
        			switch(whereNext)
        			{
        				case'address': 		self.renderAddressView();break;
        				case'prayer': 		self.renderPrayerView();break;
        				case'mygroup': 		self.renderMyGroupView();break;
        				default:self.renderAddressView();break;
        			}		
        		}
        		else{self.renderLoginView(whereNext);}
        		
        	}
        
        },
        logout: function(e){
        	console.log('Logout initiated');
        	e.preventDefault();
        	window.localStorage.clear();//clear local storage
        	
        	self.renderChooseChurch();
        	
        },
        onDateSelect: function(e) {
			//show individual blog post
			e.preventDefault();
			var date =$('#dateSelect').val();
			
			self.renderCalendarView(date);
		},
        onRotaSelect: function(e) {
			//show individual blog post
			e.preventDefault();
			var rota_id =$('#serviceSelect').val();
			
			self.renderRotaView(rota_id);
		},
        onSermonClick: function(e) {
			//play individual sermon
			e.preventDefault();
			var ID =$(this).data('tab');
			console.log('ID is '+ID);
			self.renderSermonView(ID);
		},
		onNewsClick: function(e) {
			//show individual blog post
			e.preventDefault();
			var ID =$(this).data('tab');
			self.renderPostView(ID);
		},
        onBibleClick:function(e){
        	//date picker
			e.preventDefault();
			var options = {
    			date: new Date(),
    			mode: 'date'
			};
			datePicker.show(options, dateonSuccess, dateonError);
			function dateonSuccess(date) {
    			self.renderBibleView(date);
				
			}

			function dateonError(error) { // Android only
    			alert('Error: ' + error);
			}
        
        },
        renderHomeView: function(){
		 	
   			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#home-tab-button').addClass('active');
            
            var html='<p>App not setup yet</p>';
            var storage = window.localStorage;
            html = storage.getItem('home');//pull home page data downloaded on first run.
            //logout button if logged in
            var token = storage.getItem('token');
            if(token!=""){html = html + '<p><button id="logout" data-tab="#logout" class="button">Logout</button></p>';}
            $("#page #rendered").html(html);
		},
 		renderNewsView: function() {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#news-tab-button').addClass('active');
            
          	var storage=window.localStorage;
        	var churchURL = storage.getItem('churchURL');
        	
            var token = storage.getItem('token');
            var args={'action':'ca_posts','page':1,'token':token};
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,processNews);
           	function processNews(data)
           	{
           		
           		var html='<h2>Latest news</h2><ul class="news  ui-listview">';
           		
   				for(var count = 0; count < data.length; count++)
        		{
            		var title = data[count][0];
            		var link = data[count][1];
            		var date = data[count][2];
            		var image = data[count][3];
            		var id=data[count][4];

            		html = html + '<li class="newsItem" id="'+ id +'" data-tab="'+id+'" data-target=".newsitem">';
            		html = html +'<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
            		html = html +'<img height="100" width="150" class="alignleft" src="' + image + '"><h3>' + title + '</h3><p>' + date + '<br style="clear:left;"/></p></li>';
        			
        		}
        		html= html+'</ul>';
        		html=html+'<p><input type="hidden" id="paged" value="2"/><button class="button" id="more-news">Older news</button></p>';
        		$("#page #rendered").html(html);
        		      		
			}
			$("#page #rendered").on('click', '#more-news', function()
        	{//process older posts request
        		var page=parseInt($('#paged').val());
        		var args={'action':'ca_posts','page':page,'token':token};
        		$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,function(data){
        			var html='';
        			for(var count = 0; count < data.length; count++)
        			{
            			var title = data[count][0];
            			var link = data[count][1];
            			var date = data[count][2];
            			var image = data[count][3];
            			var id=data[count][4];

            			html = html + '<li class="newsItem" id="'+ id +'" data-tab="'+id+'" data-target=".newsitem">';
            			html = html +'<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
            			html = html +'<img height="100" width="150" class="alignleft" src="' + image + '"><h3>' + title + '</h3><p>' + date + '<br style="clear:left;"/></p></li>';
        			
        			}
        			$("#page #rendered ul").append(html);
        			$("#page #rendered #paged").val(page+1);
        		});
        	});	
           	
        }, 
        renderPostView:function(ID){
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#news-tab-button').addClass('active');
            
        	var storage=window.localStorage;
        	var churchURL = storage.getItem('churchURL');
        	 var token = storage.getItem('token');
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',{ action: "ca_post",  'token':token },processPost);
        	function processPost(data)
        	{
        	
        		var html='<h3>'+data.title+'</h3>'+data.content+'<hr/>Posted by: '+data.author+' on '+data.date;
        		$("#page #rendered").html(html);
        	}
        },
        renderPrayerView:function(ID){
        	console.log('Prayer View');
            $('#firstRun').removeClass('visible');
        	$('#page').addClass('visible');
            $('.tab-button').removeClass('active');
            $('#prayer-tab-button').addClass('active');
           	
           	var storage = window.localStorage;
            var token = storage.getItem('token'); 
        	var churchURL = storage.getItem('churchURL');
        	
			$.getJSON(churchURL+'/wp-admin/admin-ajax.php',{ action: "ca_prayer",token:token },processPrayer);
        	function processPrayer(data)
        	{
        		if(data.error==='login required')self.renderLoginView('prayer');
           		else{
        				var html='<h2>Prayer</h2><ul class="prayer">';
           				for(var count = 0; count < data.length; count++)
        				{
            				var prayer=data[count]
            				html = html + '<li><h3>' + prayer.title + '</h3><p><em>Posted: ' + prayer.date + '</em></p><p>'+prayer.content+'</li>';
        				}
        				html= html+'</ul>';
        				$("#page #rendered").html(html);
        			}
        	}
        	
        },		
        renderAddressView: function() {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#address-tab-button').addClass('active');
            
            var storage = window.localStorage;
            var token = storage.getItem('token'); 
           
        	var churchURL = storage.getItem('churchURL');
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',{ action: "ca_check_token", 'token': token },checkToken);
           	function checkToken(data)
           	{
           		
           		if(data.error==='login required'){self.renderLoginView('address');}
           		else{
           			var html='<h2>Search Address List</h2>';
           			html=html+'<p><input id="s" type="text" placeholder="Who?"/></p>';
           			html = html + '<p><button id="search" data-tab="#search" class="button">Search</button></p>';

           			$("#page #rendered").html(html);
           		}
           	}
           	
        },
        renderSearchView: function(search){
        	
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#address-tab-button').addClass('active');
            
            var storage = window.localStorage;
            var token = storage.getItem('token');
            
            var churchURL = storage.getItem('churchURL');
            if(search==''){self.renderAddressView(); }//don't search if no value entered
            else
            {        
            	
            	var args={ action: "ca_search", 'token': token,'search': search};
            	
            	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,searchResult);
        		$('#page #rendered').html('<h2>Your search yielded...</h2><ul class="address  ui-listview"></ul>');
           		function searchResult(data)
           		{
           			
           			if(data.error==='login required')self.renderLoginView('address');
           			else if(data['error']==='No results')
           			{
           				var html='<h2>Address List</h2>';
           				html = html + '<h3>No results found, please try again</h3>';
           				html=html+'<input id="s" type="text" placeholder="Who?" autocorrect="off" autocapitalize="none"/><br/>';
           				html = html + '<button id="search" data-tab="#search" class="ui-btn">Search</button';
           				html = html + '</div>';
           				$("#page #rendered").html(html);
           			}
           			else
           			{
           				
           				var html='';	
           				for(var count = 0; count < data.length; count++)
        				{
        					var item=data[count];
        					html = html +	'<li class="addItem" id="'+ count + '">';
        					html = html +	'<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
        					html = html +'<h3>'+ item.name+'</h3>';
        					html = html +	 item.address + '<br/>';
        					if(item.phone){html = html + '<a href="tel:'+item.phone+'">'+item.phone +'</a><br/>';}
        					if(item.mobile)html = html + '<a href="tel:'+item.mobile+'">'+item.mobile + '</a><br/>';
        					if(item.email)html = html + '<a href="mailto:'+item.email+'">'+item.email + '</a><br/>';
        					html = html + '</div></li>';
        				}
        				$("#page #rendered  ul").append(html);
        				//add to contacts section
        				$("#page #rendered").on('click', '.addItem', function()
        				{
        						//grab contact details
        						var count=$(this).attr('id');
        						var contactItem = data[count];
        						
        						//function for found item on contacts db on device
        						function contactsSearchSuccess(contacts) {
        						
        								
        								if(contacts!='')
  										{
  										
  											//contact is in device contacts
  											navigator.notification.alert(contactItem.name+ ' is already in contacts', null, null, "Close");
  										}
  										else
  										{
  											//add contacts to device contacts
  											function contactsSaveSuccess(contact) {  navigator.notification.alert(contactItem.name+ ' saved in contacts', null, null, "Close");};
											function contactsSaveError(contactError) {alert("Error = " + contactError.code);};
											// create a new contact object
											var newContact = navigator.contacts.create();
											newContact.displayName = contactItem.name;
											newContact.nickname = item.name;// specify both to support all devices
											// populate name fields
											var name = new ContactName();
											name.givenName = contactItem.first_name;
											name.familyName = contactItem.last_name;
											newContact.name = name;
											// store contact phone numbers in ContactField[]
    										var phoneNumbers = [];
    										phoneNumbers[0] = new ContactField('home', contactItem.phone, false);
    										phoneNumbers[1] = new ContactField('mobile', contactItem.mobile, true); // preferred number
    										newContact.phoneNumbers = phoneNumbers;
											//address
											var address= new ContactAddress();
											address.type='home';
											address.streetAddress=contactItem.streetAddress;
											address.locality=contactItem.locality;
											address.region=contactItem.region;
											address.postalCode=contactItem.postalCode;
											newContact.address=address;
																					// save to device
											newContact.save(contactsSaveSuccess,contactsSaveError);
											newContact=null;
        					
  										}  
    								
								};//end success in finding contact
								function contactsSearchError(contactError) {alert('onError!');};
								// find all contacts with chosen name in any name field
								var options      = new ContactFindOptions();
								options.filter   = contactItem.name;
								options.multiple = false;
								options.desiredFields = [navigator.contacts.fieldType.id];
								options.hasPhoneNumber = true;
								var fields       = [navigator.contacts.fieldType.displayName, navigator.contacts.fieldType.name];
								navigator.contacts.find(fields, contactsSearchSuccess, contactsSearchError, options);
        				});//end of add to contacts section
           			}
           		}
        	}
        },
        renderCalendarView: function(date) {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#calendar-tab-button').addClass('active');
           	var storage = window.localStorage;
            var token = storage.getItem('token'); 
            var args={ action: "ca_cal", date: date,'token':token };
            
           	var churchURL = window.localStorage.getItem('churchURL');
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args, processCalendar);
           	
           	function processCalendar(data){
            	if(data.error== "Church Calendar is not yet set up.") {$('#page #rendered').html("<p>Church Calendar is not yet set up.</p>");}
           		else
           		{
           			//o/p structure
           			
           			var html='<h2>Calendar</h2><div id="datePicker" class="ui-field-contain">';
           			//datepicker
           			var datepicker=data.dates;
           			html= html+'<select class="tab-button" data-tab="#calendar" id="dateSelect">';
           			$.each(datepicker, function(arrayIndex, userObject){
  						html= html+'<option value="' + userObject.mysql+'" >'+ 'w/c '+userObject.friendly + '</option>';
					});
					html = html+ '</select>';
           			var calendar=data.cal;
           			html = html+'</div><ul class="calendar ui-listview" data-inset="true">';
           		
           			for(var count = 0; count < calendar.length; count++)
        			{
        				var item=calendar[count];
        			
        			
        				html = html +	'<li  class="calItem" id="'+ count + '">';
        				html = html +	'<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
						html = html +	'	<h3 class="ui-li-heading">'+ item.title+'</h3>';
						html = html +	'	<p class="ui-li-desc"><strong>'+ item.start_date+' '+ item.start_time+'-'+ item.end_time +'</strong><br/>';
						html = html +	item.description+'<br/>';
						html = html +	item.location+'</p>';
						html = html +	'</div>';
        				html = html + '</li>';
        			}	
        			html = html +'</ul>';
        			$("#page #rendered").html(html);
        		
        			$("#page #rendered").on('click', '.calItem', function()
        			{
        				//grab event
        				var count=$(this).attr('id');
        				var item = calendar[count];
        				//build calendar details
        				//start date
        				var iso = item.iso_date;
        				var sd=iso.split('-');
        				var st=item.start_time.split(":");
        				var startDate = new Date(sd[0],sd[1]-1,sd[2],st[0],st[1],0,0,0); // beware: month 0 = january, 11 = december
  						//always one day events, need to add end time to start date.
  						var end = item.end_time;
        				var et=end.split(":");
        				var endDate  = new Date(sd[0],sd[1]-1,sd[2],et[0],et[1],0,0,0);
  						var title = item.title;
  						var notes ='';
  						var eventLocation = item.location;
  						//only add event to calendar if not already in it.
  						var findSuccess=function(message) 
  						{ if(message!='')
  							{
  								//event in calendar
  								navigator.notification.alert('Event already in calendar', null, null, "Close");
  							}
  							else
  							{
  								//add event to calendar
  								var calendarSuccess = function(message) { navigator.notification.alert('Added to calendar', null, null, "Close"); };
  								var calendarError = function(message) { alert("Error: " + message); };
        						window.plugins.calendar.createEvent(title,eventLocation,notes,startDate,endDate,calendarSuccess,calendarError);
        					
  							}  					
  					 	};
  						var findError=function(message) { alert("Found Error: " + JSON.stringify(message)); };
  						window.plugins.calendar.findEvent(title,eventLocation,notes,startDate,endDate,findSuccess,findError);
  					
  					
        			});
        		};//calendar else
           	};
        },
        renderMediaView: function() {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#media-tab-button').addClass('active');
            var storage = window.localStorage;
            var token = storage.getItem('token'); 
            var churchURL = window.localStorage.getItem('churchURL');
            var args={ action: "ca_sermons", 'token': token};
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,processMedia);
           	function processMedia(data)
           	{
           		
           		var html='<h2>Latest sermons</h2><ul class="sermons ui-listview">';
           		
   				for(var count = 0; count < data.length; count++)
        		{
        			
            		var sermon=data[count];
            		html = html + '<li class="sermon" id="'+ sermon.id +'" data-tab="'+sermon.id+'" data-target=".sermon" >';
            		html = html +	'<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
            		html= html+ '<h3>' + sermon.title + '</h3><p>' + sermon.description + '<br/>'+ sermon.speaker+' on '+ sermon.pub_date + '</p>';
            		html = html+ '</div></li>';
          
        		}
        		html= html+'</ul>';
        		$("#page #rendered").html(html);
			}
        }, 
       	renderSermonView:function(ID){
       	
       		
        	var storage=window.localStorage;
        	var storage = window.localStorage;
            var token = storage.getItem('token'); 
            var churchURL = window.localStorage.getItem('churchURL');
            var args={ action: "ca_sermon", ID: ID,'token':token };
            console.log(args);
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,processPost);
        	function processPost(data)
        	{
        		
				function pad2(number) {return (number < 10 ? '0' : '') + number};
				console.log(data.file_url);
				myaudio = new Audio(data.file_url);
				console.log(myaudio);
				var isPlaying = false;
				var readyStateInterval = null;
				var html5audio = {
					
				play: function()
				{
					
					isPlaying = true;
					myaudio.play();
	
					readyStateInterval = setInterval(function(){
						if (myaudio.readyState <= 2) {
							$('#page #rendered #playbutton').hide();
							//activityIndicator.style.display = 'block';
							$('#page #rendered tesxtposition').html('Loading...');
						}
					},1000);
					myaudio.addEventListener("timeupdate", function() {
						if(isPlaying)
						{
							var s = parseInt(myaudio.currentTime % 60);
							var m = parseInt((myaudio.currentTime / 60) % 60);
							var h = parseInt(((myaudio.currentTime / 60) / 60) % 60);
							if (isPlaying && myaudio.currentTime > 0) {
							$('#page #rendered #textposition').html(pad2(h) + ':' + pad2(m) + ':' + pad2(s));
							}
						}
					}, false);
					myaudio.addEventListener("error", function() {
						console.log('myaudio ERROR');
					}, false);
					myaudio.addEventListener("canplay", function() {
						console.log('myaudio CAN PLAY');
					}, false);
					myaudio.addEventListener("waiting", function() {
						//console.log('myaudio WAITING');
						isPlaying = false;
						$('#page #rendered #playbutton').hide();
						
						$('#page #rendered #stopbutton').hide();
						//activityIndicator.style.display = 'block';
					}, false);
					myaudio.addEventListener("playing", function() {
						isPlaying = true;
						$('#page #rendered #playbutton').hide();
						//activityIndicator.style.display = 'none';
						$('#page #rendered #pausebutton').show();
						$('#page #rendered #stopbutton').show();
					}, false);
					myaudio.addEventListener("ended", function() {
						console.log('myaudio ENDED');
						html5audio.stop();
						// navigator.notification.alert('Streaming failed. Possibly due to a network error.', null, 'Stream error', 'OK');
						// navigator.notification.confirm(
						//	'Streaming failed. Possibly due to a network error.', // message
						//	onConfirmRetry,	// callback to invoke with index of button pressed
						//	'Stream error',	// title
						//	'Retry,OK'		// buttonLabels
						// );
						if (window.confirm('Streaming failed. Possibly due to a network error. Retry?')) {
							onConfirmRetry();
						}
					}, false);
				},
				pause: function() {
					isPlaying = false;
					clearInterval(readyStateInterval);
					myaudio.pause();
					$('#page #rendered #pausebutton').hide();
					$('#page #rendered #stopbutton').show();
					//activityIndicator.style.display = 'none';
					$('#page #rendered #playbutton').show();
				},
				stop: function() {
					isPlaying = false;
					clearInterval(readyStateInterval);
					if(myaudio!=null)myaudio.pause();
					$('#page #rendered #stopbutton').hide();
					//activityIndicator.style.display = 'none';
					$('#page #rendered #playbutton').show();
					$('#page #rendered #textposition').html('');
					
					myaudio = null;
					myaudio = new Audio(data.file_url);
					
				}
			}	;
				
				
				
        	
			var html = '<h2>Listen to a sermon</h2><h3>' + data.title + '</h3><p>' + data.description + '<br/>'+ data.speaker+' on '+ data.pub_date + '</p>';
        		html= html +   '<p><i class="fa fa-play fa-2x" aria-hidden="true" id="playbutton" data-tab="playbutton" data-target="#player-play"></i> <i class="fa fa-pause fa-2x" aria-hidden="true" id="pausebutton" data-tab="player-pause" data-target="#pausebutton" style="display:none"></i>  <i class="fa fa-stop fa-2x" aria-hidden="true" id="stopbutton" data-tab="player-stop" data-target="#stopbutton" style="display:none"></i></p> <div id="textpositiondiv">Time played: <span id="textposition">Stopped</span></div>';
				$("#page #rendered").html(html);
				$('#page #rendered').on('click','#playbutton',function() {console.log('Play');html5audio.play(); });
				$('#page #rendered').on('click','#pausebutton',function() {console.log('Pause');html5audio.pause(); });
        		$('#page #rendered').on('click','#stopbutton',function() {console.log('Stop');html5audio.stop(); });
				$('#page').on('click','#header-menu',function() {console.log('Navigate Away');if( myaudio!=null)html5audio.stop(); });
				$('#page').on('click','#footer-menu',function() {console.log('Navigate Away');if( myaudio!=null)html5audio.stop(); });
			}
		},
        renderRotaView: function(rota_id) {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#rota-tab-button').addClass('active');
            var storage=window.localStorage;
            var token = storage.getItem('token'); 
            var churchURL = storage.getItem('churchURL');
         
           	var args={ action: "ca_rota", rota_id: rota_id,token:token  };
    	
           
           	
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args, function(data) {
           		console.log(data);
           		if(data.error== "No one is doing anything yet") {$('#page #rendered').html("<p>The Church rota hasn't been set up yet.</p>");}
           		else
           		{
           			//o/p structure
           			$('#page #rendered').html('<h2>Rota</h2><div id="servicePicker" class="ui-field-contain"></div><table>');
           			//servicepicker
           			var servicepicker=data.services;
           			$('#page #rendered #servicePicker').append('<select class="tab-button" data-tab="#rota" id="serviceSelect">');
           			$.each(servicepicker, function(arrayIndex, userObject){
  						$('#page #rendered #servicePicker #serviceSelect ').append('<option value="' + userObject.rota_id+'" >'+ userObject.detail + '</option>');
					});
					$('#rendered #servicePicker').append('</select>');
           			
           			var tasks=data.tasks;
           			$.each(tasks, function(arrayIndex, userObject){
  						$('#page #rendered table').append('<tr><td>' + userObject.job+'</td><td>'+ userObject.people + '</td></tr>');
					});
					$('#page #rendered').append('</table>');
				}
        	});
        	
        	
        },
        renderGroupView: function() {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#group-tab-button').addClass('active');
            var storage=window.localStorage;
            var token = storage.getItem('token'); 
            var churchURL = storage.getItem('churchURL');
        	$.getJSON(churchURL+'/wp-admin/admin-ajax.php',{ action: "ca_groups",token:token },processGroups);
        	function processGroups(data)
        	{
        	
           		var html=window.localStorage.getItem('groups')+'<ul class="groups">';
           		
   				for(var count = 0; count < data.length; count++)
        		{
            		var group = data[count];
            		

            		html = html + '<li><h3>' + group.name + '</h3><p>' + group.whenwhere + '<br/>'+ group.address +'</p></li>';
        			
        		}
        		html= html+'</ul>';
        		$("#page #rendered").html(html);
           	}
        },
        renderGivingView: function() {
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#giving-tab-button').addClass('active');
            
             var html='<p>App not setup yet</p>';
            var storage = window.localStorage;
            html = storage.getItem('giving'); 
            $("#page #rendered").html(html);
        },
        renderBibleView: function(date){
			$('#firstRun').hide();
        	$('#page').show();
        	$('.tab-button').removeClass('active');
            $('#bible-tab-button').addClass('active');
            
            //get day number which works for leap years too
            if(date){var now=new Date(date);}else{var now = new Date();}

			
			var shown= now.toDateString();//readable date
			
			//retrieve readings
			var storage=window.localStorage;
            var token = storage.getItem('token'); 
            var churchURL = storage.getItem('churchURL');
			var args={ action: "ca_bible_readings", date: now,token:token };
			$.getJSON(churchURL+'/wp-admin/admin-ajax.php',args,processReadings);
        	function processReadings(data)
        	{
        		
        		var html='<h2>Bible Readings</h2>';
        		html=html+'<p class="dateShown">'+shown+'</p>';
        		html=html+'<span class="datepicker" style="text-decoration:underline">Change date</span>';
        		
        		html=html +'<p>Click passage to expand</p>';
        		if(data.length>0)
        		{
        			for(var count = 0; count < data.length; count++)
        			{
        				html=html+data[count];
            		}
            	}
            	
            	
            	$("#page #rendered").html(html);
            	$("#page #rendered .passage-toggle").on('click',function()
            	{
            		var ID=$(this).data('target');
            		$('#page #rendered .bible-text').hide();
            		$('#page #rendered #passage'+ID).toggle();
            	});
            	
        	}
        	
        },
        renderLoginView: function(whereNext){
			$('#firstRun').hide();
        	$('#page').show();
        	
        	$('.tab-button').removeClass('active');
           	
            
            var html='<h2>Please login</h2><div class="ui-content">';
            html=html+'<input type="hidden" value="'+whereNext+'" id="whereNext"/>';
			html=html+'<p><input id="username" type="text" placeholder="Enter Username" autocorrect="off" autocapitalize="none"/></p>';
			html=html+'<p><input id="password" type="password" placeholder="Enter Password"/></p>';
			html=html+'<p><button class="button" data-tab="#login" id="login">Login</button></p>';
			html=html+'<p><button class="button" data-tab="#forgotten" id="forgotten">Forgotten Password</button></p>';
			html=html+'</div>';
           	$("#page #rendered").html(html);
        }
        
       
        
    }
    controller.initialize();
    return controller;
}
