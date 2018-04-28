// Local params
// var username = '', testdata = null, userans = {testid:null, ans:{}};

$(document).on('change','.tests .select',function() {
	if ($('.tests .select').val() && $('.tests .name').val().length > 0) {
		$('.tests .next').removeAttr('disabled');
	} else {
		$('.tests .next').attr('disabled', '');
	}
});

$(document).on('keyup','.tests .name', function() {
	if ($('.tests .select').val() && $('.tests .name').val().length > 0) {
		$('.tests .next').removeAttr('disabled');
	} else {
		$('.tests .next').attr('disabled', '');
	}
});

// Select the test
$(document).on('click','.tests .next:not([disabled])', function() {
	var formdata = formobj($('.tests form'));

	http({
		url:'/inc/senddata.php?teststart',
		post:{
			formdata:formdata
		}
	}, function(data) {
		showquestion(data);
	}, this);
});

// Select the answer
$(document).on('click','.question .answer > div', function() {
	// var qid = $('.question').attr('id'), aid = $(this).attr('id');
	// userans['ans'][testdata[qid]['id']] = testdata[qid]['ans'][aid]['id'];
	$('.question .answer > div').removeAttr('selected');
	$(this).attr('selected','');
	$('.question .next').removeAttr('disabled');
});

// Can click next question if answer selected
$(document).on('click','.question .next:not([disabled])', function() {
	var answerid = $('.question .answer > div[selected]').attr('id');
	http({
		url:'/inc/senddata.php?nextquestion',
		post:{
			answerid:answerid
		}
	}, function(data) {
		showquestion(data);
	}, this);
});

// Generate question and result
function showquestion(qjson) {
	if (qjson['correctq'] == undefined) {
		var answers = '';
		var button = (qjson['currentq'] == qjson['totalq']) ? 'Pabeigt' : 'Nākamais';
		var progresslen = (100 / qjson['totalq']) * qjson['currentq'];

		for (var key in qjson['question']['ans']) {
			answers += '<div class="answer"><div id="'+ qjson['question']['ans'][key]['id'] +'">'+ qjson['question']['ans'][key]['value'] +'</div></div>';
		}

		var main = '<div class="question">'+
			'<h2>'+ qjson['question']['value'] +'</h2>'+
			'<div class="progressbar"><div class="progress" style="width:'+ progresslen +'%;"></div></div>'+
			'<div class="answers">'+
				answers+
			'</div>'+
			'<div class="next" disabled>'+ button +'</div>'+
		'</div>';

		if (qjson['currentq'] == 1) {
			$('.main').html(main);
		} else {
			$('.question h2').text(qjson['question']['value']);
			$('.question .progress').css('width', progresslen+'%');
			$('.question .answers').html(answers);
			$('.question .next').attr('disabled','').text(button);
		}
	} else {
		var main = '<div class="results">'+
			'<h2>Paldies, '+ qjson['name'] +'!</h2>'+
			'<div>Tu atbildēji pareizi uz '+ qjson['correctq'] +' no '+ qjson['totalq'] +' jautājumiem.</div>'+
			'<a href="/">Uz sākumu</a>'+
		'</div>';

		$('.main').html(main);
	}
};

// Serialize form data for fast use
function formobj(elem) {
   	var data = $(elem).serializeArray();
	var object = {};

	for (var key in data) {
	   	object[data[key]['name']] = data[key]['value'];
	}

	return object;
};

// Allow to make ajax requests currently
function http(data, callback, buttonelem) {
	var urlString = null, postQuery = null;
	var elemvars = {
	   	text:null
	};

	for (var key in data) {
	   	if (key == 'post') {
	   		postQuery = data[key];
	   	} else if (key == 'url') {
	   		urlString = data[key];
	   	}
	}

	if (buttonelem) {
 		elemvars.text = $(buttonelem).text();
 		$(buttonelem).addClass('httponload').text('...');
 	}

	$.ajax({
		type: "POST",
		url: urlString,
        data:postQuery,
        dataType: 'json',
		complete: function(){
        	if (buttonelem) {
		 		$(buttonelem).removeClass('httponload').text(elemvars.text);
		 	}
    	},
		success: function(json){
			if (typeof callback == "function") {
				callback(json);
			}
		}
	});
};
