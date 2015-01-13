var strPassword;
var charPassword;
//var complexity = $("#complexity");
var minPasswordLength = 8;
var baseScore = 0, score = 0;
var obj; 
var num = {};
num.Excess = 0;
num.Upper = 0;
num.Numbers = 0;
num.Symbols = 0;
 
var bonus = {};
bonus.Excess = 3;
bonus.Upper = 4;
bonus.Numbers = 5;
bonus.Symbols = 5;
bonus.Combo = 0;
bonus.FlatLower = 0;
bonus.FlatNumber = 0;

function init()
{
    strPassword= $(obj).val();
    charPassword = strPassword.split("");
    
    num.Excess = 0;
    num.Upper = 0;
    num.Numbers = 0;
    num.Symbols = 0;
    bonus.Combo = 0;
    bonus.FlatLower = 0;
    bonus.FlatNumber = 0;
    baseScore = 0;
    score =0;
}


function checkVal()
{
	obj = this;
	
	init();
	
    if (charPassword.length >= minPasswordLength) {
        baseScore = 50;
        analyzeString();   
        calcComplexity();      
    } else {
        baseScore = 0;
    }
     
    outputResult();
}


function analyzeString()
{  
    for (i=0; i<charPassword.length;i++) {
        if (charPassword[i].match(/[A-Z]/g)) {num.Upper++;}
        if (charPassword[i].match(/[0-9]/g)) {num.Numbers++;}
        if (charPassword[i].match(/(.*[!,@,#,$,%,^,&,*,?,_,~])/)) {num.Symbols++;}
    }
     
    num.Excess = charPassword.length - minPasswordLength;
     
    if (num.Upper && num.Numbers && num.Symbols) {
        bonus.Combo = 25;
    } else if ((num.Upper && num.Numbers) || (num.Upper && num.Symbols) || (num.Numbers && num.Symbols)) {
        bonus.Combo = 15;
    }
     
    if (strPassword.match(/^[\sa-z]+$/)) {
        bonus.FlatLower = -15;
    }
     
    if (strPassword.match(/^[\s0-9]+$/)) {
        bonus.FlatNumber = -35;
    }
}

function calcComplexity()
{
    score = baseScore + (num.Excess*bonus.Excess) + (num.Upper*bonus.Upper) + (num.Numbers*bonus.Numbers) +
(num.Symbols*bonus.Symbols) + bonus.Combo + bonus.FlatLower + bonus.FlatNumber;
}

function outputResult()
{
    if ($(obj).val()== "") { 
		//complexity.html("Enter a random value").removeClass("weak strong stronger strongest").addClass("default");
		$(obj).removeClass("password_weak password_strong password_stronger password_strongest").addClass("password_default");
	} else if (charPassword.length < minPasswordLength) {
		//complexity.html("At least " + minPasswordLength+ " characters please!");
		$(obj).removeClass("password_strong password_stronger password_strongest").addClass("password_weak");
	} else if (score < 50) {
		//complexity.html("Weak!")
		$(obj).removeClass("password_strong password_stronger password_strongest").addClass("password_weak");
	} else if (score >= 50 && score < 75) {
		//complexity.html("Average!");
		$(obj).removeClass("password_stronger password_strongest").addClass("password_strong");
	} else if (score >= 75 && score < 100) {
		//complexity.html("Strong!");
		$(obj).removeClass("password_strongest").addClass("password_stronger");
	} else if (score >= 100) {
		//complexity.html("Secure!")
		$(obj).addClass("password_strongest");
	}
}