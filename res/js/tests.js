qnum = 0;
	
function tests() {
	this.q = new Array();
//	this.okresult = 70; // определяем в ShowResult()

	this.addQuestion = function( qu ) {
		var ind = this.q.length;
		this.q[ind] = qu;
		this.q[ind].id = ind;
		qnum = ind;
		return ind;
	}
	
	// устанавливает на какой вопрос как ответили
	//this.setAnswer = function( qu, vot) {
		//this.q[qu].variant = v;
	//	this.q[qu].otvet = vot;
	//	return true;
	//}
	this.getAnswer = function( n, frm ) {
		var otv = this.q[n].get(frm);
		//if ( otv == '' ) {
		//	this.q[n].otvet = -1;
		//} else {
			this.q[n].otvet = otv;
		//}
		//window.alert( this.q[n].otvet );
		return true;
	}

	this.showQuestion = function( qn ) {
		//window.alert('question'+qu);
		return this.q[qn].show();
	}
	
	// показывает результирующую таблицу с ответами и результатом
	this.showResult = function() {
		var wndw = window.parent;
		var l = this.q.length;
		var ver = 0;
		var txt = "";
		var ret = '<div id="result_test"><h2>Результаты тестирования</h2><table>';
		ret = ret + '<tr><th>Вопрос</th><th>Текст</th><th>Ответ</th><th>Проверка</th></tr>';
		for ( i=0; i<l; i++ )		// i = номер вопроса
		{			
			var otv = this.q[i].otvet.split(',');		// ответ, который дал пользователь
			var otv_l = otv.length;
			if ( this.q[i].otvet == '' ) { otv_l = 0; }
			var v = this.q[i].v[this.q[i].variant]; 	// правильный ответ		
			ret = ret + '<tr><td>' + (i+1) + '</td><td>' + v.text + '</td><td>'; // + o.text + '</td><td>'; 
			var o = new Array();
			if ( otv_l > 0 )
			{
				if (v.type=="shortAnswer"){				
					o[0] = otv[0];
					ret = ret + o[0] + '<br />';	
				}else {
					for (j=0; j<otv.length; j++ ) {
						o[j] = v.a[otv[j]];
						ret = ret + o[j].text + '<br />';		// выводим ответ пользователя в табличку
					}
				}
			} else { ret = ret + 'вопрос пропущен'; }
			ret = ret + '</td><td>';
			
			if ( otv_l > 0 ) {
				if (v.type=="shortAnswer"){
					if (String(o[0]).toLowerCase()==String(v.a[0].otvet).toLowerCase()){
						ret = ret + 'правильно' + '<br />';
						ver++;
					}else{
						ret = ret + 'не правильно' + '<br />';
					}
				}else {
					var all = v.cnt_a;
					for (j=0; j<o.length; j++ ) {
						if ( o[j].correct ) {all--;} 
					}	
					if (all==0){
						ret = ret + 'правильно' + '<br />';
						ver++;
					}else{
						ret = ret + 'не правильно' + '<br />';
					}
										
				}
			} else { ret = ret + 'вопрос пропущен'; }
			ret = ret  +'</td></tr>';
		}
		ret = ret + '</table>';
		y = Math.round(ver * 100 / l);
		wndw.testRes = y;
//		window.parent.parent.testBar = this.okresult;
		this.okresult = wndw.testBar;
		if (this.okresult === undefined) { this.okresult = 70; }
		ret = ret + '<p>Ваш результат: '+y+ ' %</p>';
		ScormSetValue("cmi.score.raw", y);
		if ( y >= this.okresult ) { 
			ret = ret + '<p>Вы прошли тест!</p>'; 
			// пытаемся сделать так чтобы это записалось в скорм
			ScormSetValue("cmi.completion_status","completed"); //passed
		} else {
			ret = ret + '<p>Вы не прошли тест, проходной балл '+this.okresult+' %</p>'; 
			ScormSetValue("cmi.completion_status","incomplete"); //failed
		}
		ret = ret + '</div>';
		return ret;
	}

	/*
	// инициализация теста из данных в LMS 
	// возвращает номер текущего вопроса
	this.init = function() {
		var data = ScormGetValue("cmi.suspend_data");
		return 0;
	}

	// сохранение всего теста в данные LMS 
	this.save = function() {
	}
	*/
}

// определяем экземпляр теста

var test = new tests();

// класс вопроса
function getrandom( min, max ) { 
	var min_random = min; 
	var max_random = max; 
	max_random++; 
	var range = max_random - min_random; 
	var n=Math.floor(Math.random()*range) + min_random; 
	return n; 
}

function question() {
	this.id = -1;
	this.variant = -1;
	this.otvet = -1;
	this.v = new Array();

	this.show = function() {
		var min = 0;
		var max = this.v.length; 
//		window.alert( 'max - '+max );
		if ( max > 0 )
		{
			var y = getrandom( min, max-1 );
			//window.alert( 'q - '+y );
			this.variant = y;
			return this.v[y].showVariant( this.id );
		}else{
			return this.v[0].showVariant( this.id );
		}
	}
	
	this.get = function( frm ) {
		return this.v[this.variant].getReply( frm );
	}

	this.addVariant = function( variant ) {
		var ind = this.v.length;
		this.v[ind] = variant;
//		window.alert( 'добавили вариант '+ind );
		return ind;
	}
}

// класса ответов
function answer() {
	this.text = '';
	this.correct = false;
	this.otvet = '';
}

// вариант вопроса 
function variant() {
	this.text = '';
	this.type = 'single';
	this.cnt_a = 0;
	this.a = new Array();

	this.addAnswer = function( text, correct, otvet) {
		var ind = this.a.length;
		this.a[ind] = new answer;
		this.a[ind].text = text;
		if (correct==true){
			this.cnt_a++;
		}
		this.a[ind].correct = correct;
		this.a[ind].otvet = otvet;
		return ind;
	}
	
	this.getReply = function( frm ) {
		switch( this.type ) {
			case 'single':
				var value = '';
				for(i = 0; i < frm['otvet'].length; i++) {
					if(frm['otvet'][i].checked) {
							value = frm['otvet'][i].value ;
							break;
					}
				}
			break;
			case 'multiple':
				var value = '';
				var first = true;
				for(i=0; i < this.a.length; i++) {
					var item = 'otvet_'+i;
					if ( frm[item].checked ) {
						if ( !first ) { value = value + ','; }
						first = false;
						value = value + i;
					}
				}
			break;
			case 'shortAnswer' :
				var value = frm['otvet'].value ;
//				value = value.replace(/\s{2,}/g," ");
	//			value = (value[0] == " ")?value.slice(1):value;
		//		value = (value[value.length - 1] == " " || value[value.length - 1] == ".")?value.slice(0, length-1):value;
			//	var result = (answer == value)?true:false; 
			break;

		}
		return value;
	}

	this.showVariant = function( nn ) {
//		alert('номер вопроса в базе'+nn);
		var ret = '<form name="testing" id="testing">';
		//document.write('<form name="testing" id="testing">');
		//window.alert(this.type);
		switch( this.type ) {
			case 'single' : ret = ret + this.showSingle();
			//window.alert(this.type);
			break;
			case 'multiple' : ret = ret + this.showMultiple();
			//window.alert(this.type);
			break;
			case 'shortAnswer' : ret = ret + this.showShortanswer();
			//window.alert(this.type);
			break;
		}
		if (nn==0){
//			ret = ret + 'Предыдущий вопрос | <a href="javascript:gotopage('+(nn+1)+')">Следующий вопрос</a>';
			ret = ret + '<a href="javascript:gotopage('+(nn+1)+')">Следующий вопрос</a>';
		}else if (nn==qnum){
//			ret = ret + '<a href="javascript:gotopage('+(nn-1)+')">Предыдущий вопрос</a> | <a href="javascript:gotopage('+(nn+1)+')">Отправить на проверку</a>';
			ret = ret + '<a href="javascript:gotopage('+(nn+1)+')">Отправить на проверку</a>';
		}else if(nn==qnum+1){
			ret = ret;
		}else{
//			ret = ret + '<a href="javascript:gotopage('+(nn-1)+')">Предыдущий вопрос</a> | <a href="javascript:gotopage('+(nn+1)+')">Следующий вопрос</a>';
			ret = ret + '<a href="javascript:gotopage('+(nn+1)+')">Следующий вопрос</a>';
		}
		//document.write('<a href="javascript:window.parent.GoToPage('+(nn-1)+')">Назад</a> | <a href="javascript:window.parent.GoToPage('+(nn+1)+')">Next</a>');
		// window.parent.GoToPage(n)
		//document.write('</form>');
		ret = ret + '</form>';
		return ret;
	}

	this.showSingle = function() {

		//document.write('<h3>'+this.text+'</h3>');
		var ret = '<h3>'+this.text+'</h3>';
		for( i=0; i<this.a.length; i++) {
			//document.write('<input type="radio" name="otvet" id="otv_'+i+'">'+
			//	'<label for="otv_'+i+'">'+this.a[i].text+'</label><br />');
			ret = ret + '<input type="radio" name="otvet" value="'+i+'" id="otv_'+i+'" />'+'<label for="otv_'+i+'">'+this.a[i].text+'</label><br />';
		}
		return ret;
	}
	this.showMultiple = function() {

		//document.write('<h3>'+this.text+'</h3>');
		var ret = '<h3>'+this.text+'</h3>';
		for( i=0; i<this.a.length; i++) {
			//document.write('<input type="radio" name="otvet" id="otv_'+i+'">'+
			//	'<label for="otv_'+i+'">'+this.a[i].text+'</label><br />');
			ret = ret + '<input type="checkbox" name="otvet" value="'+i+'" id="otv_'+i+'" />'+'<label for="otv_'+i+'">'+this.a[i].text+'</label><br />';
		}
		return ret;
	}
	
	this.showShortanswer = function() {
		//document.write('<h3>'+this.text+'</h3>');
		var ret = '<h3>'+this.text+'</h3>';
			ret = ret + '<input type="text" name="otvet" id="otv_0" /><br/>';//+'<label for="otv_0">'+this.a[0].text+'</label><br />';
		//	ret = ret + '<input type="checkbox" name="otvet_'+i+'" id="otv_'+i+'" />'+'<label for="otv_'+i+'">'+this.a[i].text+'</label><br />';
		return ret;// + '<button id="btn">Check</button>';
	}
	
}

/* function result() {
	this.n_var = -1;
	this.n_otv = -1;
	this.n_cor = -1;
} */