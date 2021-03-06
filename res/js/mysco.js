/**/

/*function TranslateSCORM12ErrorCodeTo2004(n)
{
  if (isNaN(n)) n = parseInt(n);
  if (isNaN(n)) return "Unrecognized error value";

        switch(n)
        {
                case 0:
                case 201:
                        break;
                case 102:
                case 112:
                case 122:
                case 132:
                case 142:
                        n = 301;
                        break;
                case 402: n = 401; break;
                case 404: n = 403; break;
                case 405: n = 404; break;
                case 406: n = 406; break;
                case 407: n = 201; break
                default: n = 101;
        }
  return n
}
*/


// ========= TIME AND DURATION FUNCTIONS ========
function centisecsToISODuration(n, bPrecise)
{
  // Note: SCORM and IEEE 1484.11.1 require centisec precision
  // Parameters:
  // n = number of centiseconds
  // bPrecise = optional parameter; if true, duration will
  // be expressed without using year and/or month fields.
  // If bPrecise is not true, and the duration is long,
  // months are calculated by approximation based on average number
  // of days over 4 years (365*4+1), not counting the extra days
  // for leap years. If a reference date was available,
  // the calculation could be more precise, but becomes complex,
  // since the exact result depends on where the reference date
  // falls within the period (e.g. beginning, end or ???)
  // 1 year ~ (365*4+1)/4*60*60*24*100 = 3155760000 centiseconds
  // 1 month ~ (365*4+1)/48*60*60*24*100 = 262980000 centiseconds
  // 1 day = 8640000 centiseconds
  // 1 hour = 360000 centiseconds
  // 1 minute = 6000 centiseconds
  var str = "P";
  var nCs=n;
  var nY=0, nM=0, nD=0, nH=0, nMin=0, nS=0;
  n = Math.max(n,0); // there is no such thing as a negative duration
  var nCs = n;
  // Next set of operations uses whole seconds
  with (Math)
  {
    if (bPrecise == true)
    {
      nD = floor(nCs / 8640000);
    }
    else
    {
      nY = floor(nCs / 3155760000);
      nCs -= nY * 3155760000;
      nM = floor(nCs / 262980000);
      nCs -= nM * 262980000;
      nD = floor(nCs / 8640000);
    }
    nCs -= nD * 8640000;
    nH = floor(nCs / 360000);
    nCs -= nH * 360000;
    var nMin = floor(nCs /6000);
    nCs -= nMin * 6000
  }
  // Now we can construct string
  if (nY > 0) str += nY + "Y";
  if (nM > 0) str += nM + "M";
  if (nD > 0) str += nD + "D";
  if ((nH > 0) || (nMin > 0) || (nCs > 0))
  {
    str += "T";
    if (nH > 0) str += nH + "H";
    if (nMin > 0) str += nMin + "M";
    if (nCs > 0) str += (nCs / 100) + "S";
  }
  if (str == "P") str = "PT0H0M0S";
  // technically PT0S should do but SCORM test suite assumes longer form.
  return str;
}

function ISODurationToCentisec(str)
{
  // Only gross syntax check is performed here
  // Months calculated by approximation based on average number
  // of days over 4 years (365*4+1), not counting the extra days
  // in leap years. If a reference date was available,
  // the calculation could be more precise, but becomes complex,
  // since the exact result depends on where the reference date
  // falls within the period (e.g. beginning, end or ???)
  // 1 year ~ (365*4+1)/4*60*60*24*100 = 3155760000 centiseconds
  // 1 month ~ (365*4+1)/48*60*60*24*100 = 262980000 centiseconds
  // 1 day = 8640000 centiseconds
  // 1 hour = 360000 centiseconds
  // 1 minute = 6000 centiseconds
  var aV = new Array(0,0,0,0,0,0);
  var bErr = false;
  var bTFound = false;
  if (str.indexOf("P") != 0) bErr = true;
  if (!bErr)
  {
    var aT = new Array("Y","M","D","H","M","S")
    var p=0, i=0;
    str = str.substr(1); //get past the P
    for (i = 0 ; i < aT.length; i++)
    {
      if (str.indexOf("T") == 0)
      {
        str = str.substr(1);
        i = Math.max(i,3);
        bTFound = true;
      }
      p = str.indexOf(aT[i]);
      //alert("Checking for " + aT[i] + "\nstr = " + str);
      if (p > -1)
      {
        // Is this a M before or after T?
        if ((i == 1) && (str.indexOf("T") > -1) && (str.indexOf("T") < p)) continue;
        if (aT[i] == "S")
        {
          aV[i] = parseFloat(str.substr(0,p))
        }
        else
        {
          aV[i] = parseInt(str.substr(0,p))
        }
        if (isNaN(aV[i]))
        {
          bErr = true;
          break;
        }
        else if ((i > 2) && (!bTFound))
        {
          bErr = true;
          break;
        }
        str = str.substr(p+1);
      }
    }
    if ((!bErr) && (str.length != 0)) bErr = true;
    //alert(aV.toString())
  }
  if (bErr)
  {
    //alert("Bad format: " + str)
    return
  }
  return aV[0]*3155760000 + aV[1]*262980000
    + aV[2]*8640000 + aV[3]*360000 + aV[4]*6000
    + Math.round(aV[5]*100)
}

// Legacy functions to translate to/from SCORM 1.2 format

function SCORM12DurationToCs(str)
{
  // Format is [HH]HH:MM:SS[.SS] or maybe sometimes MM:SS[.SS]
  // Does not catch all possible errors
  // First convert to centisecs
  var a=str.split(":");
  var nS=0, n=0;
  var nMult = 1;
  var bErr = ((a.length < 2) || (a.length > 3));
  if (!bErr)
  {
    for (i=a.length-1;i >= 0; i--)
    {
      n = parseFloat(a[i]);
      if (isNaN(n))
      {
        bErr = true;
        break;
      }
      nS += n * nMult;
      nMult *= 60;
    }
  }
  if (bErr)
  {
    // alert ("Incorrect format: " + str + "\n\nFormat must be [HH]HH:MM:SS[.SS]");
    return NaN;
  }
  return Math.round(nS * 100);
}

function centisecsToSCORM12Duration(n)
{
  // Format is [HH]HH:MM:SS[.SS]
  var bTruncated = false;
  with (Math)
  {
    var nH = floor(n / 360000);
    var nCs = n - nH * 360000;
    var nM = floor(nCs / 6000);
    nCs = nCs - nM * 6000;
    var nS = floor(nCs / 100);
    nCs = nCs - nS * 100;
  }
  if (nH > 9999)
  {
    nH = 9999;
    bTruncated = true;
  }
  var str = "0000" + nH + ":";
  str = str.substr(str.length-5,5);
  if (nM < 10) str += "0";
  str += nM + ":";
  if (nS < 10) str += "0";
  str += nS;
  if (nCs > 0)
  {
    str += ".";
    if (nCs < 10) str += "0";
    str += nCs;
  }
  //if (bTruncated) alert ("Hours truncated to 9999 to fit HHHH:MM:SS.SS format")
  return str;
}



function scoWrapper() {
  this.version = '';
  this.api = null;
  //window.alert( 'From конструктор ' );
  //this.Initialize = WrapInitialize();
  //this.Terminate = WrapTerminate();
  //this.GetValue = WrapGetValue();
  //this.SetValue = WrapSetValue();
  //this.Commit = WrapCommit();
  //this.GetLastError = WrapGetLastError();
  //this.GetErrorString = WrapGetErrorString();
  //this.GetDiagnostic = WrapGetDiagnostic();
//}

// функция определена нормально
this.Initialize = function( str ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  var ret = this.api.LMSInitialize( str );
	  if ( ret ) {
		  ret = 'true';
	  } else {
		  ret = 'false';
	  }
	  return ret;
  } else {
	  return this.api.Initialize( str );
  }
}

// функция определена нормально
this.Terminate = function( str ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  var ret = this.api.LMSFinish( str );
	  if ( ret ) {
		  ret = 'true';
	  } else {
		  ret = 'false';
	  }
	  return ret;
  } else {
	  return this.api.Terminate( str );
  }
}

this.GetValue = function( param ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
		var r = "";
		var a = param.split(".")
		switch (a[0])
		{
			case "cmi":
				switch(a[1])
				{
					case "comments_from_learner":
						if ( a[3] == 'comment' ) {
							r = this.api.LMSGetValue( 'cmi.comments' );
						}
					break;
					case "comments_from_lms":
						if ( a[3] == 'comment' ) {
							r = this.api.LMSGetValue("cmi.comments_from_lms");
						}
						
					break;
					case "cmi.credit":
						r = this.api.LMSGetValue( 'cmi.core.credit' );
					break;
					case "cmi.entry":
						r = this.api.LMSGetValue( 'cmi.core.entry' );
						if (r == "ab-initio") r="ab_initio";
					break;
					case "cmi.location":
						r = this.api.LMSGetValue( 'cmi.core.lesson_location' );
					break;
					case "mode":
						r = this.api.LMSGetValue( 'cmi.core.lesson_mode' );
					break;
					case "learner_id":
						r = this.api.LMSGetValue( 'cmi.core.student_id' );
					break;
					case "learner_name":
						r = this.api.LMSGetValue( 'cmi.core.student_name' );
					break;
					case "total_time":
						r = this.api.LMSGetValue( 'cmi.core.total_time' ); // только переделать формат времени к правильному
					break;
					case "completion_status":
						r = this.api.LMSGetValue( 'cmi.core.lesson_status' );
						if (r == "not attempted") { r = 'unknown'; }
					break;
					case "":
					break;
					case "":
					break;
				}
			break;
		}
		//window.alert( 'GetValue ' + param + '   Ответ:::: '+r);
		//window.alert( 'Smotret --- GetValue ' + param + '  Otvet: '+r);
		return r; //this.api.LMSGetValue( param );
  } else {
	  return this.api.GetValue( param );
  }
}

this.SetValue = function( param, param1 ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  // переопределяем элементы среды выполнния версии 2004 к версии 12
	  
      var r = "false";
	  var a = param.split(".");
      switch (a[0]) 
	  {
		  case 'cmi' :
			  switch ( a[1] )
			  {
				case 'entry' :
					// только для чтения
				 break;	  
				case 'exit' :
					r = this.api.LMSSetValue( 'cmi.core.exit', param1 );
				 break;	  
				case 'comments_from_learner' :
					if ( a[3] == 'comment' )
					{
						r = this.api.LMSSetValue( 'cmi.comments', param1 );
					}
					// немного срезаются возможности комментариев, т.к. сохраняется тоьлко последний
				 break;	  
				case 'score' :
					if (a[2]=='scaled')
					{
						r = this.api.LMSSetValue( 'cmi.core.score.raw', param1*100 );
					} else {
						r = this.api.LMSSetValue( 'cmi.core.score.'+a[2], param1 ); // вроде так но могут возникнут не предвиденные проблемы
					}
				 break;	  
				case 'progress_measure' :
					if ( param1 == '1' )
					{
						r = this.api.LMSSetValue( 'cmi.core.lesson_status', 'completed' );
					} else {
						// пока не знаю что ставить
					}
				 break;	  
				case 'completion_status' :
					//window.alert( 'cmi.core.lesson_status - ' + param1);
					r = this.api.LMSSetValue( 'cmi.core.lesson_status', param1 ); // вроде так но не факт
					//window.alert( r );
				 break;	  
				case 'success_status' :
					r = this.api.LMSSetValue( 'cmi.core.lesson_status', param1 ); // вроде так но не факт
				 break;	  
				case 'max_time_allowed' :
					// вроде только для чтения
				 break;	  
				case 'session_time' :
					//debugger;
					//window.alert('Time: '+param1);
					var ttt = ISODurationToCentisec(param1);
					//window.alert('Time: '+ttt);
					var tttt = centisecsToSCORM12Duration(ttt);
					//window.alert('Time: '+tttt);
					r = this.api.LMSSetValue( 'cmi.core.session_time', tttt ); // только нужен правильный формат времени
				 break;	  
				case 'suspend_data' :
					r = this.api.LMSSetValue( param, param1 ); // ввести ограничение на 4000 символов
				 break;	  
				case 'location' :
					r = this.api.LMSSetValue( 'cmi.core.lesson_location', param1 );
				 break;	  
				default: 
					//window.alert( 'Default^ SetValue ' + param + ' --- ' + param1+ '   Ответ:::: '+r);
					r = this.api.LMSSetValue( param, param1 );
				 break;
			  }
			  break;
	  }
	  //window.alert( 'Smotret --- SetValue ' + param + ' --- ' + param1+ '   Otvet: '+r);
	  return r;
  } else {
	  return this.api.SetValue( param, param1 );
  }
}

// функция определена нормально
this.Commit = function( str ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  //window.alert("In Commit");
	  return this.api.LMSCommit( str );
  } else {
	  return this.api.Commit( str );
  }
}

// функция определена нормально
// если код выдается в одной ситеме то и строки будут выдаваться в соответствующей
this.GetLastError = function() {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  return this.api.LMSGetLastError();
  } else {
	  return this.api.GetLastError();
  }
}

// функция определена нормально
// если код выдается в одной ситеме то и строки будут выдаваться в соответствующей
this.GetErrorString = function( param ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  return this.api.LMSGetErrorString( param );
  } else {
	  return this.api.GetErrorString( param );
  }
}

// функция определена нормально
// если код выдается в одной ситеме то и строки будут выдаваться в соответствующей
this.GetDiagnostic = function( param ) {
  if ( this.api == null)	{ return null; }
  if ( this.version == '1.2' ) {
	  return this.api.LMSGetDiagnostic( param );
  } else {
	  return this.api.GetDiagnostic( param );
  }
}

}
//q = new scoWrapper( null, '2004'); 
//window.alert(q.version);


var gsOstynScriptVersion = "0.8.5 2006-09-30"

/*** Control variables and switches ***/
var gbAutoElapsedTime = true;
var gbAutoSuccessStatus = true;
var gbAutoCoarseCompletionStatus = true;
var gbAutoFineCompletionStatus = true;
var gbAutoTrackAllowedTime = true; // If true, keep track of allowed time
var gnAutoTrackAllowedTimePeriod = 300; // Centiseconds between time checks
var gbAutoPassingScoreIfCompleted = true;

/*** onbeforeunload can provide more reliable data saving when unloaded  ***/
var gbTerminateOnBeforeUnload = false;
  // gbTerminateOnBeforeUnload must be set to false if the SCO opens a popup window
  // or if the SCO uses Flash to load different movies.

/*** Window flag. If true, tries to close window automatically if allowed ***/
var gbEnableAutoCloseWindow = true;
  // If gbEnableAutoCloseWindow is true, try to close window automatically
  // but only if allowed - See SCORM RTE spec for allowable SCO behavior.

/*** Debug flag. If true, alerts are shown for some significant events ***/
var gbDebugSession = false; // default should be false

/*** Customizable messages ***/
// This needs to be customized only if gbAutoTrackAllowedTime is true,
// and the SCO does not provide a SCOTimeLimitDetected function
var gsTimeOutExitMessage = "The time allowed for this activity has expired. "
 + "No more data can be recorded."
var gsTimeOutContinueMessage = "The time allowed for this activity has expired. "
 + "You may continue but no more data will be recorded."

/*** Preset values that may be overridden by a query to the RTE ***/
var gnPassingScore = 1.0;
var gnCompletionThreshold = 1.0;

var gbTerminateOnBeforeUnload = false;
  // gbTerminateOnBeforeUnload must be set to false if the SCO opens a popup window
  // or if the SCO uses Flash to load different movies, or if the SCO contains
  // href="javascript:..." anchor elements, since those cause
  // spurious onbeforeunload events that would cause premature termination of
  // the communication session.

/*** Initializing before loading the page is more reliable with dynamic content  ***/
var gbInitializeBeforeLoad = true;
  // gbInitializeBeforeLoad must be set to true if the web page content will depend
  // on the SCORM API while it is loading, for example if there is an inline
  // script that writes different things to the page depending on whether the SCORM
  // environment is available.
  // If set to true, SCORMInitialize is called before the body or frameset
  // of the web page is loaded.
  // If set to false, SCORMInitialize is called only after the page has
  // been fully loaded, when the browser triggers the onload event.
  // In any case, if there is a custom handler in the SCO, it does not
  // get called until after the body or frameset has been loaded (onload event).

/*** End of control variables and switches ***/

// The functions commented out below can be defind in a SCO
// script. If they don't exist it is not a problem.
// If you need them, do not add them to this script, but
// add them to the script of your SCO. See documentation.
//function SCOSessionInitializedHandler(){;}
//function SCOSessionTerminatingHandler(){;}
//function SCOTimeLimitDetected(){;}

///////////////////////////////////////////////////////////
//////////// Do not modify anything below this ////////////
//////////// There are a lot of interdependencies /////////
///////////////////////////////////////////////////////////

//// "Private" section - data and functions used by the script functions
//// and which should not be called or inspected directly by a SCO -
//// are identified by a leading underscore in the variable name
//// or function name

/*** Variables used in session management ***/
var _gAPI = null;
var _gnScormSessionState = 0;
  // 0=not initialized yet; 1=initializing; 2=initialized; 3=terminating;
  // 4=terminated; -1=Scorm session cannot be established.
var _gbProcessingUnload = false;

/*** Variables used in management of automated behaviors ***/
var _gbScoreHasBeenSet = false;
var _gbPassingScoreAlreadyQueriedFromRTE = false; // flag for next function
var _gbPassingScoreIsFromRTE = false; // flag for next function
var _gbCompletionThresholdAlreadyQueriedFromRTE = false; // flag for next function
var _gbCompletionThresholdIsFromRTE = false; // flag for next function
var _gnAllowedTime = NaN;
var _goAllowedTimeTimer = null;
var _gnPreviousTimeInAttempt = 0;
var _gnInitCentiseconds = NaN; // initial value must be NaN
var _gnTermCentiseconds = NaN; // initial value must be NaN
var _gnTotalCentiseconds = NaN; // initial value must be NaN


/***  API communication session management functions ***
 These functions and the associated global variables allow the
 generic script to locate the API implementation, and to
 initialize and terminate the communication session automatically.

 Statements in this section allow the generic script to be invoked
 to initialize and terminate the session without having to add
 onunload, onbeforeunload and onunload handlers to the body or
 frameset element of each SCO that uses this script.
 In other words, by just including this script you can turn just
 about any web page into a SCO.

 As soon as a session is successfully initialized, the generic
 script calls a SCO function named SCOSessionInitializedHandler,
 if such a function exists. Typically, this function could exist
 in the custom script for a particular SCO that also uses this
 generic script.

 Also, just before actually terminating the session, the generic
 script's ScormTerminate function calls a SCO function named
 SCOSessionTerminatingHandler, if such a function exists.
 Typically, this function could exist in the custom script for
 a particular SCO that also uses this generic script.
*/

function _GetAPI(win)
{
	var ScanForAPI = function(win)
	{
  	var nFindAPITries = 500; // paranoid to prevent runaway
  	var objAPI = null;
	var objTmp = null;
  	var bOK = true;
  	var wndParent = null;
	var isInit = false;
	//debugger;
  	while ((!objAPI)&&(bOK)&&(nFindAPITries>0))
  	{
    	nFindAPITries--;
    	
		
		
		try 
		{ 
			objTmp = win.API_1484_11;
			if ( objTmp != null ) {  
				objAPI = new scoWrapper();
				objAPI.api = objTmp;   
				objAPI.version = '2004';
				//window.alert('2004');
				isInit = true;
			}
		} catch (e) { bOK = false; }
		
		if ( !isInit ) {
			try 
			{ 
				objTmp = win.API;
				if ( objTmp != null ) {
					objAPI = new scoWrapper();
					objAPI.api = objTmp;   
					objAPI.version = '1.2';
					//window.alert('1.2');
				}
			} catch (e) { bOK = false; }
		}
		
    	if ((!objAPI)&&(bOK))
    	{
    		try { wndParent = win.parent; } catch (e) { bOK = false; }
      	if ((!bOK)||(!wndParent)||(wndParent==win))
      	{
      		break;
      	}
      	win = wndParent;
    	}
  	}
	//window.alert('Return objAPI - version: ' + objAPI.version );
  	return objAPI;
	}

	var wndParent = null;
	var wndOpener = null;
	try { wndParent = win.parent; } catch(e) { }
	try { wndOpener = win.opener; } catch(e) { }
  if ((wndParent != null) && (wndParent != win))
  {
    _gAPI = ScanForAPI(wndParent);
  }
  if ((_gAPI == null) && (wndOpener != null))
  {
    _gAPI = ScanForAPI(wndOpener);
  }
}

/*** Generic session management functions ***/
function ScormVersion() // Hard wired - this script supports only SCORM 2004
{
  if (_gnScormSessionState > 0) return "SCORM 2004";
  return "unknown"
}

var _gbInitializeFollowUpDone = false;

function ScormInitialize()
{
  // If already initialized, there may be some follow-up to do.
  if ((!_gbInitializeFollowUpDone ) && (_gnScormSessionState == 2))
  {
    _ScormInitializeFollowUp();
  }

  // If already tried to initialize, there is nothing left to do.
  if (_gnScormSessionState != 0) return "false";

  if (gbDebugSession) alert("Attempting to initialize SCORM communication session.");

  _GetAPI(window);
  //debugger;
  if (_gAPI == null) // bug in SCORM RTE prevents ((_gAPI == null) || (_gAPI.Initialize == "undefined"))
  {
  	_gAPI = null;
    if (gbDebugSession) alert("No valid API implementation found");
  }
  else
  {
    _gnScormSessionState = 1; // State is "initializing"
	var tmp = _gAPI.Initialize("")
    if ( tmp == "true")
    {
      _gnScormSessionState = 2; // We are now "in session"
      ScormMarkInitElapsedTime(); // Keep track of when we start
      if (true)
      {
        // If SCORMInitialize is called before loading the rest of
        // the page, we can't do the follow-up yet because
        // the target for the callback into the page may not
        // be initialized yet. When the onload event fires, it will
        // call SCORMInitialize again and that will in turn
        // call the follow up function.
        if (!gbInitializeBeforeLoad)_ScormInitializeFollowUp();
      }

      // Communication session is now initialized and ready to go
      return "true";
    }
  }
  if (gbDebugSession) alert("Initialize failed");
  _gnScormSessionState = -1; // State is "error". Give up.
  return "false";
}

function _ScormInitializeFollowUp()
{
  if (_gbInitializeFollowUpDone) return;

  _gbInitializeFollowUpDone = true;

  ScormMarkInitElapsedTime(); // Keep track of when we start; update if not already set.

  // Call SCO-specific initialization handler if one exists.
  // This allows the SCO to initialize some data; for example,
  // here the SCO might get the user's name or check on
  // entry status and possible suspend data.
  if (typeof(SCOSessionInitializedHandler)=="function") SCOSessionInitializedHandler();

  // Automatic behaviors; may have been turned off by SCOSessionInitializedHandler
  if (gbAutoCoarseCompletionStatus)
  {
    var strCS = ScormGetValue("cmi.completion_status");
    if ((strCS == "unknown") || (strCS == "not attempted"))
    {
      ScormSetValue("cmi.completion_status", "incomplete");
    }
  }
  if (gbAutoTrackAllowedTime)
  {
    // redundant if (isNaN(_gnInitCentiseconds)) ScormMarkInitElapsedTime();
    _gnAllowedTime = ISODurationToCentisec(ScormGetValue("cmi.max_time_allowed"));
    if ((!isNaN(_gnAllowedTime)) && (_gnAllowedTime > 0))
    {
      _gnPreviousTimeInAttempt = ISODurationToCentisec(ScormGetValue("cmi.total_time"));
      if (_CheckTimeAllowed()) // Check immediately, may already have run out of time.
      {
        _goAllowedTimeTimer = setInterval('_CheckTimeAllowed()', gnAutoTrackAllowedTimePeriod * 10);
      }
    }
  }
}


function ScormTerminate()
{
  // Do it only if in session, and prevent reentrance.
  if (_gnScormSessionState == 2)
  {
    // if (gbDebugSession) alert("Terminating");

    _gnScormSessionState = 3; // State is "terminating"
    if (isNaN(_gnTermCentiseconds))
    {
      // If not marked already, mark time of end of session
      ScormMarkTermElapsedTime();
    }
    if (gbAutoElapsedTime)
    {
      // Calculate and send session time to RTE.
      ScormSetSessionTime(CentisecsSinceSessionStart());
    }
    if (gbAutoCoarseCompletionStatus)
    {
      var strCS = ScormGetValue("cmi.completion_status");
      if (strCS == "incomplete")
      {
        ScormSetValue("cmi.completion_status", "completed");
      }
    }
    if ((gbAutoPassingScoreIfCompleted) && (ScormGetValue("cmi.completion_status") == "completed"))
    {
      if (!_gbScoreHasBeenSet)
      {
        ScormSetValue("cmi.score.scaled","1.0");
      }
    }
    // Call SCO-specific terminating handler if one exists.
    // This allows the SCO to set any unsaved data or to override
    // data values set by automatic behaviors.
    if (typeof(SCOSessionTerminatingHandler)=="function") SCOSessionTerminatingHandler();

    // If the SCO is running in a top level window, it may be allowed
    // to close if the corresponding flag is set. If allowed, this will
    // set a timer to close the window if Terminate succeeds.
    _PrepareCloseWindowIfAllowed();

    // Now call the API implementation to terminate the session
    if (_gAPI.Terminate("") == "true")
    {
      _gnScormSessionState = 4; // State is "terminated"
      return "true";
    }
    else
    {
      // Keep trying? -- TBD
    }
  }
  return "false";
}

/*** Timekeeping function if max time allowed is being tracked ***/
function _CheckTimeAllowed()
{
  if (gbDebugSession) window.status = Math.round(CentisecsSinceSessionStart() / 100);
  if (CentisecsSinceSessionStart() + _gnPreviousTimeInAttempt >= _gnAllowedTime)
  {
    if (_goAllowedTimeTimer) clearInterval(_goAllowedTimeTimer);
    if (gbDebugSession) alert("Time out detected")
    ScormSetValue("cmi.exit","time-out");
    if (typeof(SCOTimeLimitDetected)=="function")
    {
      SCOTimeLimitDetected()
    }
    else
    {
      var sTOAction = ScormGetValue("cmi.time_limit_action");
      var sThisLoc = window.location;
      switch (sTOAction)
      {
        case "exit,message":
          alert(gsTimeOutExitMessage);
          // Try navigation request to exit when RTE evaluates Terminate
          ScormSetValue("adl.nav.request","exit");
          ScormTerminate();
          // If navigation request did not work, use brute force.
          if (window.location == sThisLoc) window.location="about:blank";
          return false;
        case "continue,message": break;
          alert(gsTimeOutContinueMessage);
          break;
        case "exit,no message": break;
          // Try navigation request to exit when RTE evaluates Terminate
          ScormSetValue("adl.nav.request","exit");
          ScormTerminate();
          // If navigation request did not work, use brute force.
          if (window.location == sThisLoc) window.location="about:blank";
          return false;
        default: break;
      }
    }
  }
  return true;
}

/***  Load and unload event management functions ***
 These functions allow the generic script to be invoked
 to initialize and terminate the session without having to add
 onunload and onunload handlers to the body or frameset element
 of the actual SCO. In other words, by just including this
 script these functions can turn just about any web page into a SCO.
 The existing onload, onbeforeunload and onunload handlers
 that may be specified in the body or frameset tag for the web
 page are preserved. See the note about precedence below.
*/


// Relay function for onload event
function _Scorm_InitSession()
{
  ScormInitialize();
}

// Relay function for onunload event
function _Scorm_TerminateSession()
{
  //alert("unload detected");
  _gbProcessingUnload = true;
  ScormTerminate();
  return;
}

// Relay function for onbeforeunload event
function _Scorm_TerminateSessionBeforeUnload()
{
  //alert("onbeforeunload detected");
  if (gbTerminateOnBeforeUnload)
  {
    _gbProcessingUnload = true;
    ScormTerminate();
  }
  // One cannot use this event to prevent unloading without
  // causing serious problems, therefore this function
  // must specify explicitly that it has no return value.
  return;
}

// Important difference in behavior between IE and Firefox
// In IE, the event handler added by this script will execute
// after the event handler defined in the body tag, if any.
// In FF, the event handler added by this script will execute
// before the event handler defined in the body tag, if any.

// Inspired by http://www.tek-tips.com/faqs.cfm?fidH62
function AddLoadAndUnloadEvents()
{
  var sfL = "_Scorm_InitSession";
  var sfU = "_Scorm_TerminateSession";
  var sfB = "_Scorm_TerminateSessionBeforeUnload";
  var fL = window._Scorm_InitSession;
  var fU = window._Scorm_TerminateSession;
  var fB = window._Scorm_TerminateSessionBeforeUnload;
  if (typeof(window.addEventListener) != "undefined")
  {
    // alert("addEventListener") // this fires off in FireFox
    window.addEventListener("load", fL, false );
    window.addEventListener("unload", fU, false );
    if (gbTerminateOnBeforeUnload) window.addEventListener("beforeunload", fB, false);
  }
  else if (typeof(window.attachEvent) != "undefined" )
  {
    // alert("attachEvent") // this fires off in IE 6
    window.attachEvent("onload", fL);
    window.attachEvent("onunload", fU);
    if (gbTerminateOnBeforeUnload) window.attachEvent("onbeforeunload", fB, false);
  }
  {
    var oldFunc;
    if (window.onload != null)
    {
      oldFunc = window.onload;
      window.onload = function ( e ) {
        oldFunc( e );
        fL();
      };
    }
    else
    {
      window.onload = fL;
    }
    if (window.onunload != null)
    {
      oldFunc = window.onunload;
      window.onunload = function ( e ) {
        oldFunc( e );
        fU();
      };
    }
    else
    {
      window.onunload = fU;
    }
    if (window.onbeforeunload != null)
    {
      oldFunc = window.onbeforeunload;
      window.onbeforeunload = function ( e ) {
        oldFunc( e );
        fB();
      };
    }
    else
    {
      window.onbeforeunload = fB;
    }
  }
}

AddLoadAndUnloadEvents();

/*** End load and unload event management functions ***/

/*** General session info helper functions ***/

function ScormIsInSession()
{
  // Returns true is SetValue and GetValue are allowed
  return ((_gnScormSessionState == 2) || (_gnScormSessionState == 3));
}

function ScormGetSessionState()
{
  return _gnScormSessionState;
}

function ScormGetLastError()
{
  var nErr = -1;
  if ((_gAPI) && (typeof(_gAPI.GetLastError) != undefined)) nErr = _gAPI.GetLastError();
  return nErr;
}
function ScormGetErrorString(nErr)
{
  var strErr = "SCORM API not available";
  if (_gAPI)
  {
    // Note: Get Error functions may work even if the session is not open
    // (to help diagnose session management errors), but we're still careful,
    // and so we check whether each function is available before calling it.
    if ((isNaN(nErr)) && (typeof(_gAPI.GetLastError) != undefined)) nErr = _gAPI.GetLastError();
    if (typeof(_gAPI.GetErrorString) != undefined) strErr = _gAPI.GetErrorString(nErr.toString());
  }
  return strErr;
}

function ScormGetDiagnostic(str)
{
  var strR = "";
  if (_gAPI)
  {
    strR = _gAPI.GetDiagnostic(str.toString());
  }
  return strR;
}

/*** General data helper functions ***/
function ScormGetValue(what, bIgnoreError)
{
  // bIgnoreError flag is set to true only when this function is used
  // for testing, for example to query a value that does not exist yet.
  var strR = "";
  //debugger;
  if (ScormIsInSession())
  {
    strR = _gAPI.GetValue(what);
    if ((!bIgnoreError) && (gbDebugSession) && (strR=="") && (ScormGetLastError()!=0))
    {
      alert("GetValue Error:\nParam='" + what +
        "'\n\nError=" + ScormGetLastError() + "\n" + ScormGetErrorString());
    }
  }
  return strR;
}

function ScormSetValue(what, value)
{
  var err = "false"
  if (ScormIsInSession())
  {
    err = _gAPI.SetValue(what, value.toString());
    if ((gbDebugSession) && (err == "false"))
    {
      alert("SetValue Error:\nParam1='" + what + "'\n\nParam2='" + value +
        "'\n\nError=" + ScormGetLastError() + "\n" + ScormGetErrorString());
    }
    if (err == "true")
    {
      // Additional auto behaviors for certain data elements
       if ((what=="cmi.score.scaled")&&(err=="true"))
      {
        _gbScoreHasBeenSet = true; // set flag in case auto score is enabled
        if (gbAutoSuccessStatus==true)
        {
           ScormSetSuccessStatusForScore(parseFloat(value.toString()));
        }
      }
      else if ((what=="cmi.progress_measure")&&(err=="true")&&(gbAutoFineCompletionStatus==true))
      {
        ScormSetCompletionStatusForMeasure(parseFloat(value.toString()));
      }
    }
  }
  return err;
}

function ScormCommit()
{
  if (ScormIsInSession())
  {
    return _gAPI.Commit("");
  }
  return "false";
}

/*** Interaction helper functions ***/
var _gaInteractionIndexCache = new Array();

function ScormInteractionAddRecord (strID, strType)
{
  if (!ScormIsInSession()) return -1;
  var n = ScormInteractionGetIndex(strID);
  if (n > -1) // An interaction record exists with this identifier
  {
    if (ScormGetValue("cmi.interactions." + n + ".type") != strType) return -1;
    return n;
  }
  n = ScormInteractionGetCount();
  var strPrefix = "cmi.interactions." + n + ".";
  if (ScormSetValue(strPrefix + "id", strID) != "true") return -1;
  if (ScormSetValue(strPrefix + "type", strType) != "true") return -1;
  _IndexCacheAdd (_gaInteractionIndexCache,n,strID);
  return n
}

function ScormInteractionGetCount()
{
  var r = parseInt(ScormGetValue("cmi.interactions._count"));
  if (isNaN(r)) r = 0;
  return r;
}

function ScormInteractionGetData(strID, strElem)
{
  var n = ScormInteractionGetIndex(strID);
  if (n < 0)
  {
    return ""; // No interaction record exists with this identifier
  }
  return ScormGetValue("cmi.interactions." + n + "." + strElem);
}

function ScormInteractionGetIndex(strID)
{
  var i = _IndexCacheGet (_gaInteractionIndexCache,strID);
  if (i >= 0) return i;
  var n = ScormInteractionGetCount();
  for (i = 0; i < n; i++)
  {
    if (ScormGetValue("cmi.interactions." + i + ".id") == strID)
    {
      _IndexCacheAdd (_gaInteractionIndexCache,i,strID);
      return i;
    }
  }
  return -1;
}

function ScormInteractionSetData(strID, strElem, strVal)
{
  var n = ScormInteractionGetIndex(strID);
  var r = "true";
  if (n < 0)
  {
    return "false"; // No interaction record exists with this identifier
  }
  // Possible optimization -- don't set value if that is already the value
  //if (ScormGetValue("cmi.interactions." + n + "." + strElem) != strVal)
  //{
    r = ScormSetValue("cmi.interactions." + n + "." + strElem, strVal);
  //}
  return r
}

//// Objective helper functions ////

function _IndexCacheGet (aCache,strID)
{
  for (i=0;i<aCache.length;i++)
  {
    if (aCache[i][1] == strID) return aCache[i][0];
  }
  return -1;
}
function _IndexCacheAdd (aCache,n,strID)
{
  for (i=0;i<aCache.length;i++)
  {
    if (aCache[i][1] ==strID) return;
  }
  aCache[aCache.length]=new Array(n,strID);
}

var _gaObjectiveIndexCache = new Array();

function ScormObjectiveAddRecord (strID)
{
  var n = ScormObjectiveGetIndex(strID);
  if (n > -1) // An objective record exists with this identifier
  {
    return n;
  }
  n = ScormObjectiveGetCount();
  var strPrefix = "cmi.objectives." + n + ".";
  if (ScormSetValue(strPrefix + "id", strID) != "true") return -1;
  _IndexCacheAdd (_gaObjectiveIndexCache,n,strID);
  return n
}

function ScormObjectiveGetCount()
{
  var r = parseInt(ScormGetValue("cmi.objectives._count"));
  if (isNaN(r)) r = 0;
  return r;
}

function ScormObjectiveGetData(strID, strElem)
{
  var n = ScormObjectiveGetIndex(strID);
  if (n < 0)
  {
    return ""; // No objectiverecord exists with this identifier
  }
  return ScormGetValue("cmi.objectives." + n + "." + strElem);
}

function ScormObjectiveGetIndex(strID)
{
  var i = _IndexCacheGet (_gaObjectiveIndexCache,strID);
  if (i >= 0) return i;
  var n = ScormObjectiveCount();
  for (i = 0; i < n; i++)
  {
    if (ScormGetValue("cmi.objectives." + i + ".id") == strID)
    {
      _IndexCacheAdd (_gaObjectiveIndexCache,i,strID);
      return i;
    }
  }
  return -1;
}

function ScormObjectiveSetData(strID, strElem, strVal)
{
  var n = ScormObjectiveGetIndex(strID);
  if (n < 0) // If no objective record with this ID
  {
    n = ScormObjectiveAddRecord(strID);
    if (n < 0) return "false"; // No objective record and failed to create one
  }
  return ScormSetValue("cmi.objectives." + n + "." + strElem, strVal);
}

//// comments_from_learner helper function ////

function ScormCommentFromLearnerAddRecord (comment,location)
{
  // Location is optional. Timestamp is added automatically.
  var n = ScormCommentFromLearnerGetCount();
  var r = "";
  var strPrefix = "cmi.comments_from_learner." + n + ".";
  if ((comment) && (comment.length > 0))
  {
    r = ScormSetValue(strPrefix + "comment", comment+"");
  }
  if ((r != "false") && (location) && (location.length > 0))
  {
    r = ScormSetValue(strPrefix + "location", location+"");
  }
  if ((r != "false") && (location) && (location.length > 0))
  {
    r = ScormSetValue(strPrefix + "timestamp", MakeISOtimeStamp());
  }
  if (r == "true") return r;
  return "false";
}

function ScormCommentFromLearnerFindRecordIndex(location)
{
  // Finds a comment from learner index based on location
  if ((!location) || (location.length == 0)) return -1;
  var nCnt = ScormCommentFromLearnerGetCount();
  for (var i=0;i<nCnt;i++)
  {
    if (ScormGetValue("cmi.comments_from_learner." + n + ".location") == location)
    {
      return n;
    }
  }
  return -1;
}

function ScormCommentFromLearnerFindRecordIndex(location)
{
  // Finds a comment from learner based on location
  var n = ScormCommentFromLearnerFindRecordIndex(location);
  if (n > -1)
  {
    return (ScormGetValue("cmi.comments_from_learner." + n + ".comment"));
  }
  return "";
}

function ScormCommentFromLearnerReplaceRecord (comment,location)
{
  // Location is required, because it is used to identify the
  // record to replace. Timestamp is added automatically.
  var n = ScormCommentFromLearnerFindRecordIndex(location)
  var r;
  if (n > -1)
  {
    var strPrefix = "cmi.comments_from_learner." + n + ".";
    r = ScormSetValue(strPrefix + "comment", comment+"");
    ScormSetValue(strPrefix + "timestamp", MakeISOtimeStamp());
  }
  else
  {
    r = ScormCommentFromLearnerAddRecord (comment,location);
  }
  return r;
}

function ScormCommentFromLearnerGetCount()
{
  var r = parseInt(ScormGetValue("cmi.comments_from_learner._count"));
  if (isNaN(r)) r = 0;
  return r;
}


function ScormCommentFromLMSGetCount()
{
  var r = parseInt(ScormGetValue("cmi.comments_from_lms._count"));
  if (isNaN(r)) r = 0;
  return r;
}

// Data shaping functions for various CMI values

function _IsValidScaledScore(n)
{
  return ((!isNaN(n)) && (n >= -1.0) && (n <= 1.0))
}

function _IsValidProgressMeasure(n)
{
  return ((!isNaN(n)) && (n >= -1.0) && (n <= 1.0))
}

function ScormSetSuccessStatusForScore(nScore)
{
  if (!_gbPassingScoreAlreadyQueriedFromRTE)
  {
    var n = parseFloat(ScormGetValue("cmi.scaled_passing_score"));
    _gbPassingScoreAlreadyQueriedFromRTE = true;
    if (_IsValidScaledScore(n))
    {
      gnPassingScore = n;
      _gbPassingScoreIsFromRTE = true;
    }
  }
  if ((_IsValidScaledScore(nScore)) && (_IsValidScaledScore(gnPassingScore)))
  {
    ScormSetValue("cmi.success_status",(nScore >= gnPassingScore)?"passed":"failed");
  }
}

function ScormSetCompletionStatusForMeasure(nMeas)
{
  if (!_gbCompletionThresholdAlreadyQueriedFromRTE)
  {
    var nThreshold = parseFloat(ScormGetValue("cmi.completion_threshold"));
    _gbCompletionThresholdAlreadyQueriedFromRTE = true;
    if (_IsValidProgressMeasure(n))
    {
      gnCompletionThreshold = n;
      _gbCompletionThresholdIsFromRTE = true;
    }
  }
  if (_IsValidProgressMeasure(nMeas))
  {
    gbAutoCoarseCompletionStatus = false;
    if (_IsValidProgressMeasure(gnCompletionThreshold))
    {
      if (nMeas >= gnCompletionThreshold)
      {
        ScormSetValue("cmi.completion_status","completed");
      }
      else if (nMeas == 0)
      {
         ScormSetValue("cmi.success_status","not attempted");
      }
      else
      {
         ScormSetValue("cmi.success_status","incomplete");
      }
    }
  }
}

/*** End data shaping and validation functions ***/

/*** TimeStamp helper function. Returns a timestamp in ISO format ***/

function MakeISOTimeStamp(objDate, bRelative, nResolution)
{
  // Make an ISO 8601 timestamp string as specified for SCORM 2004
  // * objDate is an optional ECMAScript Date object;
  //   if objDate is null, "this instant" is assumed.
  // * bRelative is optional; if bRelative is true,
  //   the timestamp will show local time with a time offset from UTC;
  //   otherwise the timestamp will show UTC (a.k.a. Zulu) time.
  // * nResolution is optional; it specifies max decimal digits
  //   for fractions of second; it can be null, 0 or 2. If null, 2 is assumed.
  var nMs, nCs = 0;
  var s = "";
  if (objDate)
  {
    if ((typeof(objDate)).indexOf("date") < 0) objDate = null;
  }
  if (!objDate) objDate = new Date();
  if (bRelative) nMs = objDate.getMilliseconds();
  else nMs = objDate.getUTCMilliseconds();
  if (nResolution == 0)
  {
    // Precision is whole seconds; round up if necessary
    if (nMs > 500)
    {
      if (bRelative) objDate.setMilliseconds(1000);
      else objDate.setUTCMilliseconds(1000);
    }
  }
  else
  {
    // Default precision is centisecond. Let us see whether we need to add
    // a rounding up adjustment
    if (nMs > 994)
    {
      if (bRelative) objDate.setMilliseconds(1000);
      else objDate.setUTCMilliseconds(1000)
    }
    else
    {
      nCs = Math.floor(nMs / 10);
    }
  }
  if (bRelative)
  {
    s = objDate.getFullYear() + "-" +
    ZeroPad(objDate.getMonth(), 2) + "-" +
    ZeroPad(objDate.getDate(), 2) + "T" +
    ZeroPad(objDate.getHours(), 2) + ":" +
    ZeroPad(objDate.getMinutes(), 2) + ":" +
    ZeroPad(objDate.getSeconds(),2);
  }
  else
  {
    s = objDate.getUTCFullYear() + "-" +
    ZeroPad(objDate.getUTCMonth(), 2) + "-" +
    ZeroPad(objDate.getUTCDate(), 2) + "T" +
    ZeroPad(objDate.getUTCHours(), 2) + ":" +
    ZeroPad(objDate.getUTCMinutes(), 2) + ":" +
    ZeroPad(objDate.getUTCSeconds(),2);
  }
  if (nCs > 0)
  {
    s += "." + ZeroPad(nCs,2);
  }
  if (bRelative)
  {
    // Need to flip the sign of the time zone offset
    var nTZOff = -objDate.getTimezoneOffset();
    if (nTZOff >= 0) s += "+";
    s += ZeroPad(Math.round(nTZOff / 60), 2);
    nTZOff = nTZOff % 60;
    if (nTZOff > 0) s += ":" +  ZeroPad(nTZOff, 2);
  }
  else
  {
    s += "Z";
  }
  return s;
}

function ZeroPad(n, nLength)
{
  // Takes a number and pads it with leading 0 to the length specified.
  // The padded length does not include negative sign if present.
  var bNeg = (n < 0);
  var s = n.toString();
  if (bNeg) s = s.substr(1,s.length);
  while (s.length < nLength) s = "0" + s;
  if (bNeg) s = "-" + s;
  return s
}

function DateFromISOString(strDate)
{
  // Convert an ISO 8601 formatted string to a local date
  // Returns an ECMAScript Date object or null if an error was detected
  // Assumes that the string is well formed and SCORM conformant
  // otherwise a runtime error may occur in this function.
  var objDate = new Date();
  var sDate = strDate; // The date part of the input, after a little massaging
  var sTime = null; // The time part of the input, if it is included
  var sTimeOffset = null; // UTC offset, if specified in the input string
  var sTimeOffsetSign = "";
  var a = null; // Will be reused for all kinds of string splits
  var n, nY, nM, nD, nH, nMin, nS, nCs = 0;

  // If this is "Zulu" time, it will make things a little easier
  var bZulu = (strDate.indexOf("Z") > -1);
  if (bZulu) strDate = strDate.substr(0, strDate.length - 1);

  // Parse the ISO string into date and time
  if (strDate.indexOf("T") > -1)
  {
    var a = strDate.split("T");
    sDate = a[0];
    var sTime = a[1];
  }
  // Parse the date part
  a = sDate.split("-");
  nY = parseInt(a[0]);
  if (a.length > 1) nM = parseInt(a[1]);
  if (a.length > 2) sD = a[2];
  // If this was only a date but with no time, there might still be an offset
  if ((sTime == null) && (a.length == 4))
  {
    // There is a negative time offset but no time (assume midnight)
    sTimeOffset = a[3];
    sTimeOffsetSign = "-";
  }
  else if ((a.length == 3) && (sD.indexOf("+")> -1))
  {
    a = sD.split("+");
    sD = a[0];
    sTimeOffset = a[1];
    sTimeOffsetSign = "+";
  }
  var nD = parseInt(sD);
  // Done with the date. If there is a time part, parse it out.
  if (sTime)
  {
    if (sTime.indexOf("-")) sTimeOffsetSign = "-";
    if (sTime.indexOf("+")) sTimeOffsetSign = "+";
    if (sTimeOffsetSign != "")
    {
      a = sTime.split(sTimeOffsetSign);
      sTime = a[0];
      sTimeOffset = a[1];
    }
    a = sTime.split(":");
    nH = parseInt(a[0]);
    if (a.length > 1) nMin = parseInt(a[1]);
    if (a.length > 2)
    {
      nSec = parseFloat(a[2]);
      if (isNaN(nSec)) return null;
      nCs = Math.round(nSec / 100);
      nSec = Math.round(nSec - (nCs * 100));
    }
  }
  if (bZulu)
  {
    objDate.setUTCFullYear(nY,nM,nD);
    objDate.setUTCHours(nH,nMin,nSec,nCs * 10);
  }
  else
  {
    objDate.setFullYear(nY,nM,nD);
    objDate.setHours(nH,nMin,nSec,nCs * 10);

    // Calculate and set the time offset for local time
    if (sTimeOffset)
    {
      var nOffset = 0;
      a = sTimeOffset.split(":");
      nOffset = parseInt(a[0]);
      if (isNaN(nOffset)) return null;
      nOffset = nOffset * 60
      if (a.length > 1)
      {
        n = parseInt(a[1]);
        if (isNaN(n)) return null;
        nOffset += n;
      }
      nOffset = nOffset * 60; // minutes to milliseconds
      if (sTimeOffsetSign == "-") nOffset = -nOffset;
      objDate.setTime(objDate.getTime() + nOffset);
    }
  }
  return objDate //.toString();
}


/***  Timekeeping management functions ***
 These functions allow the generic script to keep track
 of elapsed time automatically. Some of these functions
 can also be used as helper functions to
*/

function ScormMarkInitElapsedTime()
{
  // Called by ScormInitialize when successful;
  var d = new Date();
  _gnInitCentiseconds  = Math.round((new Date()).getTime() / 10);
  return _gnInitCentiseconds;
}

function ScormMarkTermElapsedTime()
{
  // Called by ScormTerminate
  _gnTermCentiseconds  = Math.round((new Date()).getTime() / 10);
  return _gnTermCentiseconds;
}

function CentisecsSinceSessionStart()
{
  if (isNaN(_gnInitCentiseconds)) return 0;
  if ((isNaN(_gnTermCentiseconds)) || (_gnTermCentiseconds == 0))
  {
    return Math.round((new Date()).getTime() / 10) - _gnInitCentiseconds;
  }
  return _gnTermCentiseconds - _gnInitCentiseconds;
}

function CentisecsSinceAttemptStart()
{
  var n = 0;
  if (isNaN(_gnTotalCentiseconds))
  {
    n = ISODurationToCentisec(ScormGetValue("cmi.total_time"))
    if (!isNaN(n)) _gnTotalCentiseconds = n;
  }
  n = CentisecsSinceSessionStart();
  if (!isNaN(_gnTotalCentiseconds))
  {
    return _gnTotalCentiseconds + n;
  }
  return n;
}

function ScormSetSessionTime(nCentisec)
{
  if (isNaN(nCentisec)) nCentisec = CentisecsSinceSessionStart();
  //if (gbDebugSession) alert("Centisecs since session start: " + nCentisec);
  return ScormSetValue("cmi.session_time",centisecsToISODuration(nCentisec));
}

// Helper functions for duration
function centisecsToISODuration(n) {
    // Note: SCORM and IEEE 1484.11.1 require centisec precision
    // Months calculated by approximation based on average number
  // of days over 4 years (365*4+1), not counting the extra day
  // every 1000 years. If a reference date was available,
  // the calculation could be more precise, but becomes complex,
    // since the exact result depends on where the reference date
    // falls within the period (e.g. beginning, end or ???)
  // 1 year ~ (365*4+1)/4*60*60*24*100 = 3155760000 centiseconds
  // 1 month ~ (365*4+1)/48*60*60*24*100 = 262980000 centiseconds
  // 1 day = 8640000 centiseconds
  // 1 hour = 360000 centiseconds
  // 1 minute = 6000 centiseconds
  n = Math.max(n,0); // there is no such thing as a negative duration
    var str = "P";
    var nCs = n;
    // Next set of operations uses whole seconds
    var nY = Math.floor(nCs / 3155760000);
    nCs -= nY * 3155760000;
    var nM = Math.floor(nCs / 262980000);
    nCs -= nM * 262980000;
    var nD = Math.floor(nCs / 8640000);
    nCs -= nD * 8640000;
    var nH = Math.floor(nCs / 360000);
    nCs -= nH * 360000;
    var nMin = Math.floor(nCs /6000);
    nCs -= nMin * 6000
    // Now we can construct string
    if (nY > 0) str += nY + "Y";
    if (nM > 0) str += nM + "M";
    if (nD > 0) str += nD + "D";
    if ((nH > 0) || (nMin > 0) || (nCs > 0)) {
        str += "T";
        if (nH > 0) str += nH + "H";
        if (nMin > 0) str += nMin + "M";
    if (nCs > 0) str += (nCs / 100) + "S";
    }
    if (str == "P") str = "PT0H0M0S";
      // technically PT0S should do but SCORM test suite assumes longer form.
    return str;
}

function ISODurationToCentisec(str)
{
  // Only gross syntax check is performed here
  // Months calculated by approximation based on average number
  // of days over 4 years (365*4+1), not counting the extra day
  // every 1000 years. If a reference date was available,
  // the calculation could be more precise, but becomes complex,
  // since the exact result depends on where the reference date
  // falls within the period (e.g. beginning, end or ???)
  // 1 year ~ (365*4+1)/4*60*60*24*100 = 3155760000 centiseconds
  // 1 month ~ (365*4+1)/48*60*60*24*100 = 262980000 centiseconds
  // 1 day = 8640000 centiseconds
  // 1 hour = 360000 centiseconds
  // 1 minute = 6000 centiseconds
  var aV = new Array(0,0,0,0,0,0);
  var bErr = false;
  var bTFound = false;
  if (str.indexOf("P") != 0) bErr = true;
  if (!bErr)
  {
    var aT = new Array("Y","M","D","H","M","S")
    var p=0;
    var i = 0;
    str = str.substr(1); //get past the P
    for (i = 0 ; i < aT.length; i++)
    {
      if (str.indexOf("T") == 0)
      {
        str = str.substr(1);
        i = Math.max(i,3);
        bTFound = true;
      }
      p = str.indexOf(aT[i]);
      //alert("Checking for " + aT[i] + "\nstr = " + str);
      if (p > -1)
      {
        // Is this a M before or after T? Month or Minute?
        if ((i == 1) && (str.indexOf("T") > -1) && (str.indexOf("T") < p)) continue;
        if (aT[i] == "S")
        {
          aV[i] = parseFloat(str.substr(0,p))
        }
        else
        {
          aV[i] = parseInt(str.substr(0,p))
        }
        if (isNaN(aV[i]))
        {
          bErr = true;
          break;
        }
        else if ((i > 2) && (!bTFound))
        {
          bErr = true;
          break;
        }
        str = str.substr(p+1);
      }
    }
    if ((!bErr) && (str.length != 0)) bErr = true;
    //alert(aV.toString())
  }
  if (bErr)
  {
     //alert("Bad format: " + str)
    return 0
  }
  return aV[0]*3155760000 + aV[1]*262980000
      + aV[2]*8640000 + aV[3]*360000 + aV[4]*6000
      + Math.round(aV[5]*100)
}

/*** End timekeeping management functions ***/

/*** Window auto close management functions ***/

var _gTimerOwnWindowClose = null;
var _gbAlreadyTriedToCloseOwnWindow = false;

function _IsClosingWindowOK()
{
  if (!gbEnableAutoCloseWindow) return false;
  // Tweaking of the rule may be required for some LMS
  // that use what looks like a popup window but is actually a frameset.
  // A function to try to detect such a situation might be inserted here.
  return (!((window.parent) && (window.parent || window)));
}

function _PrepareCloseWindowIfAllowed()
{
  if ((!_gbAlreadyTriedToCloseOwnWindow)
      && (!_gbProcessingUnload)
      && (_IsClosingWindowOK()))
  {
    gTimerWindowClose = setInterval("_CloseTheSCOWindow()", 1500);
  }
}

function _CloseTheSCOWindow()
{
  if (_gTimerOwnWindowClose)
  {
    clearInterval(_gTimerOwnWindowClose);
    _gTimerOwnWindowClose = null;
  }
  if (_gbAlreadyTriedToCloseOwnWindow) return;
  _gbAlreadyTriedToCloseOwnWindow = true;
  if (!window.closed) window.close();
}
/*** End Window Management functions ***/


/*** Dynamic initialization before onload event ***/

if (gbInitializeBeforeLoad)
{
  ScormInitialize();
}

/*** Used for debugging ***/

function ostyn2004scoScriptOK()
{
  alert ("ostyn2004sco.js OK\nVersion: " + gsOstynScriptVersion);
}

