function buildCal(m, y, cM, admin, tournoi){
var mn=['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
var dim=[31,0,31,30,31,30,31,31,30,31,30,31];

var oD = new Date(y, m-1, 1); //DD replaced line to fix date bug when current day is 31st
oD.od=oD.getDay()+1; //DD replaced line to fix date bug when current day is 31st

var todaydate=new Date() //DD added
var curmonth=todaydate.getMonth()+1;
var curyear=todaydate.getFullYear();
var scanfortoday=(y==todaydate.getFullYear() && m==todaydate.getMonth()+1)? todaydate.getDate() : 0 //DD added

dim[1]=(((oD.getFullYear()%100!=0)&&(oD.getFullYear()%4==0))||(oD.getFullYear()%400==0))?29:28;

if (m == 1) {m2=12;y2=y-1;} else {m2=m-1; y2=y;}
var nD = new Date(y2, m2-1, 1);
dim2 = (m2 == 2) ? ((((nD.getFullYear()%100!=0)&&(nD.getFullYear()%4==0))||(nD.getFullYear()%400==0))?29:28) : dim[m2-1];

var t='<div class="'+cM+'">';
t+='<div class="calheader mdl-card__title mdl-color--primary"><div class="calcontrol calprevmonth"><button class="mdl-button mdl-js-button mdl-button--icon" onclick="return cal_prev('+m+', '+y+', '+admin+', '+tournoi+')"><i class="material-icons">fast_rewind</i></button></div><div class="calcontrol calnextmonth"><button class="mdl-button mdl-js-button mdl-button--icon" onclick="return cal_next('+m+', '+y+', '+admin+', '+tournoi+')" /><i class="material-icons">fast_forward</i></button></div><a href="#" onclick="return cal_go('+curmonth+', '+curyear+', '+admin+', '+tournoi+')"><div class="caltitle">'+mn[m-1]+' '+y+'</div></a></div>';

t+='<div class="cal_wrapper">';

t+='<div class="weekbox weekboxname">';
for(s=0;s<7;s++)t+='<div id="cal_day_name_'+s+'" class="daybox dayboxname">'+"DimLunMarMerJeuVenSam".substr((s*3),3)+'</div>';
t+='</div>';

t+='<div class="calweekswrapper"><div class="weekbox">';
for(i=1;i<=42;i++){
var isincal=((i-oD.od>=0)&&(i-oD.od<dim[m-1]));
var x=isincal ? i-oD.od+1 : (i-oD.od+1 > 0 ? (i-oD.od+1-dim[m-1]) : (i-oD.od+1+dim2));
t+='<div id="cal_day_'+(isincal ? '1' : '0')+'_'+x+'"  class="daybox daybox_'+(i%7)+' '+(isincal ? 'dayinmonth' : 'dayoutmonth')+' '+((x==scanfortoday && isincal)? 'dayselected' : ' ')+' ">';
t+='<div class="dayboxdate">'+x+'</div>';
t+='<div class="dayboxvalue"></div>';
t+='</div>';
if(((i)%7==0)&&(i<36))t+='</div><div class="weekbox">';
}
t+='</div>';

return t+='</div></div>';

}
