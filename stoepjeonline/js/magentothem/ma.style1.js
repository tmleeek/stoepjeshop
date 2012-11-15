$bn1.fn.fadeSlideShow = function(options) {
	return this.each(function(){
		settings = $bn1.extend({
     		width: 640, // default width of the slideshow
     		height: 480, // default height of the slideshow
			speed: 'slow', // default animation transition speed
			interval: 3000, // default interval between image change
			PlayPauseElement: '', // default css id for the play / pause element
			PlayText: 'Play', // default play text
			PauseText: 'Pause', // default pause text
			NextElement: '', // default id for next button - bnNext
			NextElementText: 'Next >', // default text for next button
			PrevElement: '', // default id for prev button - bnPrev
			PrevElementText: '< Prev', // default text for prev button
			ListElement: '', // default id for image / content controll list - bnList
			ListLi: 'bnLi', // default class for li's in the image / content controll 
			ListLiActive: 'bnActive', // default class for active state in the controll list
			addListToId: false, // add the controll list to special id in your code - default false
			allowKeyboardCtrl: true, // allow keyboard controlls left / right / space
			autoplay: true // autoplay the slideshow
	 	}, options);
		
		// set style for wrapper element
		$bn1(this).css({
			width: settings.width,
			height: settings.height,
			position: 'relative',
			overflow: 'hidden'
		});
		
		// set styles for child element
		$bn1('> *',this).css({
			position: 'absolute',
			width: settings.width,
			height: settings.height
		});
		
		// count number of slides
		Slides = $bn1('> *', this).length;
		Slides = Slides - 1;
		ActSlide = Slides;
		// Set $bn1 Slide short var
		jQslide = $bn1('> *', this);
		// save this
		fssThis = this;
		
		autoplay = function(){
			intval = setInterval(function(){
				jQslide.eq(ActSlide).fadeOut(settings.speed);
				
				// if list is on change the active class
				if(settings.ListElement){
					setActLi = (Slides - ActSlide) + 1;
					if(setActLi > Slides){setActLi=0;}
					$bn1('#'+settings.ListElement+' li').removeClass(settings.ListLiActive);
					$bn1('#'+settings.ListElement+' li').eq(setActLi).addClass(settings.ListLiActive);
				}
				
				if(ActSlide <= 0){
					jQslide.fadeIn(settings.speed);
					ActSlide = Slides;
				}else{
					ActSlide = ActSlide - 1;	
				}
			}, settings.interval);
			
			if(settings.PlayPauseElement){
				$bn1('#'+settings.PlayPauseElement).html(settings.PauseText);
			}
		}
		
		stopAutoplay = function(){
			clearInterval(intval);
			intval = false;
			if(settings.PlayPauseElement){
				$bn1('#'+settings.PlayPauseElement).html(settings.PlayText);
			}
		}
		
		jumpTo = function(newIndex){
			if(newIndex < 0){newIndex = Slides;}
			else if(newIndex > Slides){newIndex = 0;}
			if( newIndex >= ActSlide ){
				$bn1('> *:lt('+(newIndex+1)+')', fssThis).fadeIn(settings.speed);
			}else if(newIndex <= ActSlide){
				$bn1('> *:gt('+newIndex+')', fssThis).fadeOut(settings.speed);
			}
			
			// set the active slide
			ActSlide = newIndex;

			if(settings.ListElement){
				// set active
				$bn1('#'+settings.ListElement+' li').removeClass(settings.ListLiActive);
				$bn1('#'+settings.ListElement+' li').eq((Slides-newIndex)).addClass(settings.ListLiActive);
			}
		}
		
		// if list is on render it
		if(settings.ListElement){
			i=0;
			li = '';
			while(i<=Slides){
				if(i==0){
					li = li+'<li class="'+settings.ListLi+i+' '+settings.ListLiActive+'"><a href="#">'+(i+1)+'<\/a><\/li>';
				}else{
					li = li+'<li class="'+settings.ListLi+i+'"><a href="#">'+(i+1)+'<\/a><\/li>';
				}
				i++;
			}
			List = '<ul id="'+settings.ListElement+'">'+li+'<\/ul>';
			
			// add list to a special id or append after the slideshow
			if(settings.addListToId){
				$bn1('#'+settings.addListToId).append(List);
			}else{
				$bn1(this).after(List);
			}
			
			$bn1('#'+settings.ListElement+' a').bind('click', function(){
				index = $bn1('#'+settings.ListElement+' a').index(this);
				stopAutoplay();
				ReverseIndex = Slides-index;
				
				jumpTo(ReverseIndex);
				
				return false;
			});
		}
		
		if(settings.PlayPauseElement){
			if(!$bn1('#'+settings.PlayPauseElement).css('display')){
				$bn1(this).after('<a href="#" id="'+settings.PlayPauseElement+'"><\/a>');
			}
			
			if(settings.autoplay){
				$bn1('#'+settings.PlayPauseElement).html(settings.PauseText);
			}else{
				$bn1('#'+settings.PlayPauseElement).html(settings.PlayText);
			}
			
			$bn1('#'+settings.PlayPauseElement).bind('click', function(){
				if(intval){
					stopAutoplay();
				}else{
					autoplay();
				}
				return false;
			});
		}
		
		if(settings.NextElement){
			if(!$bn1('#'+settings.NextElement).css('display')){
				$bn1(this).after('<a href="#" id="'+settings.NextElement+'">'+settings.NextElementText+'<\/a>');
			}
			
			$bn1('#'+settings.NextElement).bind('click', function(){
				nextSlide = ActSlide-1;
				stopAutoplay();
				jumpTo(nextSlide);
				return false;
			});
		}
		
		if(settings.PrevElement){
			if(!$bn1('#'+settings.PrevElement).css('display')){
				$bn1(this).after('<a href="#" id="'+settings.PrevElement+'">'+settings.PrevElementText+'<\/a>');
			}
			
			$bn1('#'+settings.PrevElement).bind('click', function(){
				prevSlide = ActSlide+1;
				stopAutoplay();
				jumpTo(prevSlide);
				return false;
			});
		}
		
		if(settings.allowKeyboardCtrl){
			$bn1(document).bind('keydown', function(e){
				if(e.which==39){
					nextSlide = ActSlide-1;
					stopAutoplay();
					jumpTo(nextSlide);
				}else if(e.which==37){
					prevSlide = ActSlide+1;
					stopAutoplay();
					jumpTo(prevSlide);
				}else if(e.which==32){
					if(intval){stopAutoplay();}
					else{autoplay();}
					return false;
				}
			});
		}
		
		// start autoplay or set it to false
		if(settings.autoplay){autoplay();}else{intval=false;}
	});
};