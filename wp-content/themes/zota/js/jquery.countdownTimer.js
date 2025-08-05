(function($){
	jQuery.fn.tbayCountDown = function( options ) {
	 	return this.each(function() {
			new jQuery.tbayCountDown( this, options ); 
		});
 	}
	jQuery.tbayCountDown = function( obj, options ) {
		this.options = jQuery.extend({
			autoStart : true,
			LeadingZero:true,
			DisplayFormat:"<div>%%D%% Days</div><div>%%H%% Hours</div><div>%%M%% Minutes</div><div>%%S%% Seconds</div>",
			FinishMessage:"Expired",
			CountActive:true,
			TargetDate:null
		}, options || {} );
		if ( this.options.TargetDate == null || this.options.TargetDate == '' ){
			return ;
		}
		this.timer  = null;
		this.element = obj;
		this.CountStepper = -1;
		this.CountStepper = Math.ceil(this.CountStepper);
		this.SetTimeOutPeriod = (Math.abs(this.CountStepper)-1)*1000 + 990;
		var dthen = new Date(this.options.TargetDate);
		var dnow = new Date();
		var ddiff = this.CountStepper > 0 ? new Date(dnow-dthen) : new Date(dthen-dnow);
		var gsecs = Math.floor(ddiff.valueOf()/1000); 
		this.CountBack(gsecs, this);
	};
	jQuery.tbayCountDown.fn = jQuery.tbayCountDown.prototype;
    jQuery.tbayCountDown.fn.extend = jQuery.tbayCountDown.extend = jQuery.extend;
	jQuery.tbayCountDown.fn.extend({
		calculateDate:function( secs, num1, num2 ){
			var s = ((Math.floor(secs/num1))%num2).toString();
			if ( this.options.LeadingZero && s.length < 2) {
				s = "0" + s;
			}
			return "<span>" + s + "</span>";
		},
		CountBack:function( secs, self ){
			if (secs < 0) {
				self.element.innerHTML = '<div class="lof-labelexpired"> '+self.options.FinishMessage+"</div>";
				return;
			}
			clearInterval(self.timer);
			var DisplayStr = self.options.DisplayFormat.replace(/%%D%%/g, self.calculateDate( secs,86400,100000) );
			DisplayStr = DisplayStr.replace(/%%H%%/g, self.calculateDate(secs,3600,24));
			DisplayStr = DisplayStr.replace(/%%M%%/g, self.calculateDate(secs,60,60));
			DisplayStr = DisplayStr.replace(/%%S%%/g, self.calculateDate(secs,1,60));
			self.element.innerHTML = DisplayStr;
			if (self.options.CountActive) {
				self.timer = null;
				self.timer =  setTimeout( function(){
					self.CountBack((secs+self.CountStepper),self);			
				},( self.SetTimeOutPeriod ) );
			}
		}
	});
})(jQuery)