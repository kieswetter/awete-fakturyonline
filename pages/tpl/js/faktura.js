var oItemError = new Object;
  oItemError.odb_nazev = "Pole Název je povinné!";
  oItemError.odb_ulice = "Pole Název je povinné!";
  oItemError.odb_mesto = "Pole Název je povinné!";
  oItemError.odb_psc = "Pole Název je povinné!";
  oItemError.odb_ico = "Pole Název je povinné!";
  oItemError.odb_dic = "Pole Název je povinné!";
  oItemError.odb_email = "Pole Název je povinné!";
  oItemError.odb_tel = "Pole Název je povinné!";
  
  oItemError.dod_nazev = "Pole Název je povinné!";
  oItemError.dod_ulice = "Pole Název je povinné!";
  oItemError.dod_mesto = "Pole Název je povinné!";
  oItemError.dod_psc = "Pole Název je povinné!";
  oItemError.dod_ico = "Pole Název je povinné!";
  oItemError.dod_dic = "Pole Název je povinné!";
  oItemError.dod_web = "Pole Název je povinné!";
  oItemError.dod_email = "Pole Název je povinné!";
  oItemError.dod_tel = "Pole Název je povinné!";
  oItemError.dod_fax = "Pole Název je povinné!";
  oItemError.dod_dph = "Pole Název je povinné!";  
  
  oItemError.cislo_faktury = "Pole Název je povinné!";
  oItemError.variabilni_symbol = "Pole Název je povinné!";
  oItemError.vystavil_tel = "Pole Název je povinné!";
  
  
var oItemCheck = new Object;
    oItemCheck.number = new Array('dod_ico','dod_dic','odb_ico','odb_dic','cislo_faktury','variabilni_symbol');
    oItemCheck.text = new Array('dod_nazev','dod_ulice','dod_mesto','odb_nazev','odb_ulice','odb_mesto');
    oItemCheck.email = new Array('dod_email','odb_email');
    oItemCheck.phone = new Array('dod_tel','odb_tel','dod_fax','vystavil_tel');
    oItemCheck.psc = ['dod_psc','odb_psc'];
    oItemCheck.web = ['dod_web'];
    
    oItemCheck.radio = ['dod_dph'];

var oItemCheckRequired = new Array( 'dod_nazev','dod_ulice','dod_mesto','dod_psc','dod_ico','dod_dph',
                                    'odb_nazev','odb_ulice','odb_mesto','odb_psc'
                                  );


var oItemsPolozkaCheck = {'text':'text','pocet':'posnumber','cena':'price'};

var check_email = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
var check_number = /^(\d)+$/;
var check_posnumber = /^([1-9])+(\d)*$/;
var check_price = /^(\d)+((\.)?(\d)+)?$/; /// 12.50
var check_psc = /^(\d)+(\s)?(\d)+$/;
var check_phone = /^(\+)?([0-9\s])+$/;
var check_text = /^(\w)+([\s-\w])*$/;
var check_web = /^(http:\/\/)?(www.)?(([a-zA-Z0-9-_])+.)+([A-Za-z]){2,4}$/;
    
var aCalendars = new Array;
  aCalendars.push('datum_vystaveni');  
  aCalendars.push('datum_splatnosti');

/** definice html promenych, co se vlklada **/
var htmlOddItem = '<a href="#" class="odd_item" title="Odstranit položku"><span>odstranit položku</span></a>';
var htmlEditItem = '<a href="#" class="edit_item" title="Editovat položku"><span>editovat položku</span></a>';
var htmlSubmitItem = '<a href="#" id="submit_item" title="Potvrdit záznam"><span>Potvrdit záznam</span></a>';
var htmlInputZdanPlneni = '<input type="hidden" name="datum_zdan_plneni" id="datum_zdan_plneni">';
var htmlErrorMsgBlockStart = '<span class="error_msg">';
var htmlErrorMsgBlockEnd = '</span>';

var htmlSelectDphOptions = '<option value="0">0</option><option value="10">10</option><option value="20">20</option>';
/** konecdefinice html promenych, co se vlklada **/

var inputNameSufixCelkemItem = '_cenacelkemzapolozku';
var inputNameSufixCelkemItemDph = '_cenacelkemzapolozkudph';

/// umozni event change u splatnosti a datumu ///
var bChangePlatba = true;
var nSplatnost = 0;
/// dopocitani datumu vystaveni nebo datumu splatnosti ///
var bLockDatumSplatnosti = false;
/// umozni dopsat variabilni symbol dle cisla faktury ///
var bRefillVarSymbol = true;
var bCountWithDph = false;
var bPolozkaError = false;

  
jQ(document).ready(function(){ 
  
 //########################### definice udalosti ########################//
  
  /// automaticky check vsech inputu pri jejich zmene ///
  jQ("input[type!='hidden']").blur(function(){
    var req = false;
    for(var i in oItemCheck){
      if(i == 'radio')
        continue;
      for(j in oItemCheck[i]){
        if(oItemCheck[i][j] == jQ(this).attr('name')){
          /// hodnota je ok ///
          if(eval('check_'+i+'.test("'+jQ(this).attr('value')+'")')){            
            removeErrorMsg(jQ(this));
          }else{
            for(r in oItemCheckRequired){
              if(oItemCheckRequired[r] == oItemCheck[i][j]){
                req = true;
                break;
              }
            }
            if(req || !req && jQ(this).attr('value').length > 0)
              addErrorMsg(jQ(this));
          } 
          break;
        }
      }
    }
  });
  
  /// pri prvni zmene cisla faktury zmenit var.symbol ///
  jQ("input[name='cislo_faktury']").change(function(){
    if(bRefillVarSymbol && check_number.test(jQ(this).attr('value'))){
      bRefillVarSymbol=false;
      jQ("input[name='variabilni_symbol']").attr('value',jQ(this).attr('value'));
    }
  });  
  jQ("input[name='variabilni_symbol']").change(function(){
    bRefillVarSymbol=false;    
  });
    
  /// zmena selectu zpusobu uhrady nebo datumu ///
  jQ("select[name='zpusob_uhrady'],input[name='datum_vystaveni'],input[name='datum_splatnosti'],input[name='splatnost']").change(function(){
    if(bChangePlatba)
      setDatesOfPayment(jQ(this).attr('name'));
  });
  
  /// uzamknuti datumu ///
  jQ("#lockDatumSplatnosti").click(function(){
    /// je uzamceno ///
    if(bLockDatumSplatnosti){
      bLockDatumSplatnosti = false;
      jQ(this).text('zamknout');
      jQ(this).attr('class','unlock');
    }else{
      bLockDatumSplatnosti = true;
      jQ(this).text('odemknout');
      jQ(this).attr('class','lock');
    }
  });  
  
  jQ("#btnsub_save,#btnsub_print,#btnsub_pdf").click(function(){ 
	 if(checkForm()){
		 jQ("input[name='fc_faktura_save']").attr('value',jQ(this).attr('name'));
		 //jQ("form[name='fc_faktura']").submit();
		 return true;
	 }else{
		 return false;
	 }
  });  
  
  /// pridani dalsi polozky v seznamu fakturacnich polozek ///
  jQ("#add_item").click(function(){
    var lastItem = jQ("#list_of_items tr[id^='item']").length;
    var countItems =  lastItem + 1;
             
    jQ("#list_of_items tr:last").before('<tr id="item'+countItems+'"><td class="cislo">'+countItems+'</td><td class="popis"><input type="hidden" name="item'+countItems+'_text" value=""><span></span></td><td class="pocet"><input type="hidden" name="item'+countItems+'_pocet" value="1"><span>1</span></td><td class="cena"><input type="hidden" name="item'+countItems+'_cena" value="0"><span>0</span><span>Kč</span></td><td class="dph_hid"><input type="hidden" name="item'+countItems+'_dph" value="10"><span>10</span><span>%</span></td><th class="dph_hid"><input type="hidden" name="item'+countItems+inputNameSufixCelkemItem+'" value="0"><span>0</span><span>Kč</span></th><th class="cena_pol"><input type="hidden" name="item'+countItems+inputNameSufixCelkemItemDph+'" value="0"><span>0</span><span>Kč</span></th><td class="icons">'+htmlOddItem+' | '+htmlEditItem+'</td></tr>');
    
    setDphVisibility();
    
    setEventsForPolozka('item'+countItems);
    // zobrazeni editace polozky ihned po jejim vzniku ///
    jQ("tr[id='item"+countItems+"'] .edit_item").click();
    
    return false;
  });  
  
  /// onchange radio inputs DPH - zobrazeni nebo schovani radku s datumem dan plneni///
  jQ("input[name='dod_dph']").change(function(){   
    setBlockZdaneniPlneni(jQ(this).attr('value'));
    setDphVisibility();
  });
  
  /// onchange vyberu odberatele ///
  jQ("#odb_id").change(function(){
	  var odbInp = new Array('nazev','ulice','mesto','psc','ico','dic','email','tel');
	  if(jQ(this).val() == 'null'){
		 for(var i in odbInp) {
			 jQ("input[name='odb_"+odbInp[i]+"']").removeAttr('readonly');
			 jQ("input[name='odb_"+odbInp[i]+"']").attr('value','');
		 }
	 }else{
		 for(var i in odbInp) {
			 jQ("input[name='odb_"+odbInp[i]+"']").attr('readonly','readonly');
			 jQ("input[name='odb_"+odbInp[i]+"']").attr('value',aOdberatele[jQ(this).val()][odbInp[i]]);
			 jQ("input[name='odb_"+odbInp[i]+"']").blur();
		 }
	 }
  });
  
  //##################### uvodni akce #################################//  
  /// nasetovani kalendaru pro datumy ///
  for(var i in aCalendars)
  {
    jQ("#"+aCalendars[i]).datepicker({dateFormat: 'd.m.yy'});
    date = new Date();
    value = jQ("#"+aCalendars[i]).attr('value').split(".");
    date.setDate(value[0]);
    date.setMonth(value[1]-1);
    date.setYear(value[2]);
    jQ("#"+aCalendars[i]).datepicker('setDate', date);

  }
  
  /// schovani radku datum zdanitelneho plneni kdyz je Ne///
  if(!jQ("input[name='dod_dph']").attr('checked'))
    setBlockZdaneniPlneni('Ne');
  else
    setBlockZdaneniPlneni('Ano');
  
  //setDphVisibility();
  jQ("#add_item").click();
  
  /// uvodni prepocitani polozek a cen - duvod pro vynulovani///
  recountItems(); 
});

/*********************************************************************************/
/***************************** FUNCTIONS ****************************************/
function setBlockZdaneniPlneni(value)
{
  if(value == 'Ano'){
    jQ("#tr_datum_zdan_plneni").show();      
    jQ("#tr_datum_zdan_plneni td").prepend(htmlInputZdanPlneni);      
    jQ("#datum_zdan_plneni").attr('value',jQ("#datum_vystaveni").attr('value'));      
    jQ("#td_datum_zdan_plneni").html('<span>'+jQ("#datum_vystaveni").attr('value')+'</span>');
  }else{
    jQ("#datum_zdan_plneni").remove();
    jQ("#tr_datum_zdan_plneni").hide();
  }
}

function checkForm()
{
  var nErrors = 0;
  //var oErrors = new Object;
  var sRequired = ","+oItemCheckRequired.toString()+",";  
  
  jQ(".error_msg").remove();
  for(var i in oItemCheck)
  {
    for (j in oItemCheck[i])
    {
      value = jQ("input[name='"+oItemCheck[i][j]+"'],select[name='"+oItemCheck[i][j]+"']").attr('value');
      removeErrorMsg("input[name='"+oItemCheck[i][j]+"']");
      if(value == undefined) {
    	  continue;
      }
      /// povinne hodnoty ///
      if(sRequired.indexOf(","+oItemCheck[i][j]+",") > -1){        
        if(oItemCheck[i][j] == 'dod_dph'){
          rad = document.fc_faktura.dod_dph;          
          if(!rad[0].checked && !rad[1].checked){
            nErrors++;
            addErrorMsg(rad);
          }          
        }else if(!eval('check_'+i+'.test("'+value+'")')){
          nErrors++;
          addErrorMsg(jQ("input[name='"+oItemCheck[i][j]+"']"));
        }
      /// nepovinne hodnoty ///
      }else{
        if(value.length > 0 && !eval('check_'+i+'.test("'+value+'")')){          
          nErrors++;
          addErrorMsg(jQ("input[name='"+oItemCheck[i][j]+"']"));
        }
      }      
    }
  }
  if(nErrors == 0){
	  return true;
  }else{	  
	  jQ("#fak_buttony_save").prepend('<div class="error_msg">Počet chyb na stránce: '+nErrors);
	  return false;
  }
  
}

function setEventsForPolozka(trID)
{
  /// odstraneni polozky ///
  jQ("#list_of_items #"+trID+" .odd_item").click(function(){
    jQ("#"+trID).remove();//(this).parent().parent().remove();    
    reorderItems();
    jQ(".odd_item,.edit_item,#add_item").show();
    return false;
  });
  
  /// zmena polozky ///
  jQ("#list_of_items #"+trID+" .edit_item").click(function(){
    var trEl = jQ("#"+trID); //jQ(this).parent().parent();    
    var trId = trID;//jQ(trEl).attr('id');
    
    jQ(trEl).children("td").each(function(){
      jQ(this).children("span:first").hide();
      
      /// zobrazeni inputu pro editaci ///
      jQ(this).children("input[name^='"+trId+"']").each(function(){
        var name = jQ(this).attr('name');
        var sufName = name.substr(name.indexOf("_")+1);
        var editName = trId + '_edit_' + sufName;
        //alert(name + "\n" + name.substr(name.indexOf("_")));
        
        if(name.substr(name.indexOf("_")) == '_dph'){          
          jQ(this).after('<select name="'+editName+'">'+htmlSelectDphOptions+'</select>');          
          jQ(this).parent().children("select").children("option[value="+jQ(this).attr('value')+"]").attr('selected','selected');
        }else{
          jQ(this).after('<input name="'+editName+'" value="'+jQ(this).attr('value')+'">');
          
          /*
          /// event pro osetreni inputu pri editaci polozky ///
          jQ("input[name='"+editName+"']").blur(function(){
            //alert('check_'+oItemsPolozkaCheck[sufName]+'.test('+jQ(this).attr('value')+')');
            if(!eval('check_'+oItemsPolozkaCheck[sufName]+'.test("'+jQ(this).attr('value')+'")')){
              addErrorMsg(jQ(this));
              bPolozkaError = true;               
            }else{
              removeErrorMsg(jQ(this));
            }              
          });
          */
        }
                
      });
          
    });
    /// osetreni inputu polozky ///    
    
    jQ(".odd_item,.edit_item, #add_item").hide();
    jQ(this).parent().children(".odd_item").show();
    
    /// pridani submit prvku ///
    jQ(this).after(htmlSubmitItem);    
    /// potvrzeni zmen polozky ///
    jQ("#submit_item").click(function(){
      var error = false;
      for(var i in oItemsPolozkaCheck)
      {
        ele = jQ("input[name='"+trId+"_edit_"+i+"']");
        removeErrorMsg(ele);
        if( !eval('check_'+oItemsPolozkaCheck[i]+'.test("'+jQ(ele).attr('value')+'")') ){
          addErrorMsg(ele);
          error = true;
        }else if(i == 'cena' && Number(jQ(ele).attr('value'))<=0){          
          addErrorMsg(ele);
          error = true;
        }
      }
      if(error)
        return false;
        
      /// predani hodnot schovanym inputum ///
      jQ(trEl).children("td").each(function(){
        jQ(this).children("input[name^='"+trId+"_edit'],select[name^='"+trId+"_edit']").each(function(){
          var editEl = jQ(this);
          //alert(jQ(editEl).length);
          var name = jQ(editEl).attr('name');
          //alert(name);
          var targetName = trId + name.substr(name.indexOf("_edit_")+5);
          jQ("input[name='"+targetName+"']").attr('value',jQ(editEl).attr('value'));
          jQ(this).parent().children("span:first").text(jQ(editEl).attr('value'));          
          jQ(editEl).remove();       
        });
        jQ(this).children("span:first").show();
            
      });
      
      /// prepocitani polozky ///
      recountItem(trId);
            
      jQ(".odd_item,.edit_item, #add_item").show();
      jQ(this).remove();
      return false;
    });
        
    return false;
  }); 
}

function reorderItems()
{
  var items = jQ("#list_of_items tr[id^='item']");
  var oldId;
  for(var i=1;i<=items.length;i++)
  {
    jQ(items[i-1]).attr('id','item'+i);
    
    jQ(items[i-1]).children("td,th").children("input[name^='item']").each(function(index){
      name=jQ(this).attr('name');
      jQ(this).attr('name','item'+i+name.substr(name.indexOf("_")));  
    });
    jQ(items[i-1]).children("td:first").text(i);  
  }
  
  recountItems();
}

function recountItem(trId)
{
  var trEl = jQ("#list_of_items tr[id='"+trId+"']");
  
  var pocet = jQ("input[name='"+trId+"_pocet']").attr('value');
  var cena = jQ("input[name='"+trId+"_cena']").attr('value');
  var dph = jQ("input[name='"+trId+"_dph']").attr('value');
  total = Math.round(pocet*cena*100)/100;
  totalDph = Math.round(total*(1+dph/100)*100)/100;
  
  /// pocita se bez DPH ///
  if(!bCountWithDph)
    totalDph = total;
  
  jQ("input[name='"+trId+inputNameSufixCelkemItem+"']").attr('value',total);
  jQ("input[name='"+trId+inputNameSufixCelkemItem+"']").parent().children("span:first").text(total);
  
  jQ("input[name='"+trId+inputNameSufixCelkemItemDph+"']").attr('value',totalDph);
  jQ("input[name='"+trId+inputNameSufixCelkemItemDph+"']").parent().children("span:first").text(totalDph);
  
  recountItems();
}

function recountItems()
{
  //var dph0 = 0;
  var dph10 = 0;
  var dph20 = 0;
  var dph = 0;
  var cena = 0;
  var cenadph = 0;
  
  jQ("#list_of_items tr[id^='item']").each(function(){
    itemId = jQ(this).attr('id');
    dphVal = Number(jQ("input[name='"+itemId+"_dph']").attr('value'));
    cenaVal = Number(jQ("input[name='"+itemId+inputNameSufixCelkemItem+"']").attr('value'));
    cenadphVal = Number(jQ("input[name='"+itemId+inputNameSufixCelkemItemDph+"']").attr('value'));
    
    dph12Val = Math.round((cenadphVal - cenaVal)*100)/100;
    if(dphVal == 10){
      dph10 += dph12Val;
    }else if(dphVal == 20){
      dph20 += dph12Val;
    }/*else if(dphVal == 0){
      dph0 += dph12Val;
    }*/
    cena += Math.round(cenaVal*100)/100;
    cenadph += Math.round(cenadphVal*100)/100;
    //alert(dph12Val + "\n" + dphVal + "\n" + "\n" + cenaVal + "\n" + cenadphVal);
  });
  
  dph = Math.round((dph10 + dph20)*100)/100;
  cena = Math.round(cena*100)/100;
  cenadph = Math.round(cenadph*100)/100;
  
  /// pocita se bez DPH ///
  if(!bCountWithDph)
    cenadph = cena;    
 // alert(dph10 + "\n" + dph20 + "\n" + dph + "\n" + cena + "\n" + cenadph);
  //jQ("input[name='complete_dph0']").attr('value',dph0);
  // jQ("input[name='complete_dph0']").parent().children("span:first").text(dph0);
  jQ("input[name='complete_dph10']").attr('value',dph10);
  jQ("input[name='complete_dph10']").parent().children("span:first").text(dph10);
  jQ("input[name='complete_dph20']").attr('value',dph20);
  jQ("input[name='complete_dph20']").parent().children("span:first").text(dph20);
  jQ("input[name='complete_dph']").attr('value',dph);
  jQ("input[name='complete_dph']").parent().children("span:first").text(dph);
  jQ("input[name='complete_cena']").attr('value',cena);
  jQ("input[name='complete_cena']").parent().children("span:first").text(cena);
  jQ("input[name='complete_cenadph']").attr('value',cenadph);
  jQ("input[name='complete_cenadph']").parent().children("span:first").text(cenadph);
  
}

function setDatesOfPayment(inpName)
{
  bChangePlatba = false;
  item = jQ("select[name='"+inpName+"'],input[name='"+inpName+"']");
  var datVyst = jQ("#datum_vystaveni").datepicker('getDate');
  var datSplat = jQ("#datum_splatnosti").datepicker('getDate');  
  switch(inpName)
  {
    case 'splatnost':
      if(check_number.test(jQ(item).attr('value'))){
        nSplatnost = Number(jQ(item).attr('value'));        
      }
      setDatesOfPayment('zpusob_uhrady');
      return;
    break;
    case 'zpusob_uhrady':
      if(jQ(item).attr('value') == 'Hotově'){
        nSplatnost = 0;
      }else if(nSplatnost == 0){
        nSplatnost = 14;
      }
      if(bLockDatumSplatnosti){
        datVyst = datSplat;
        datVyst.setDate(datSplat.getDate()-nSplatnost);
        jQ("#datum_vystaveni").datepicker('setDate',datVyst);        
      }else{
        datSplat = datVyst;
        datSplat.setDate(datVyst.getDate()+nSplatnost);
        jQ("#datum_splatnosti").datepicker('setDate',datSplat);
      }
    break;
    case 'datum_vystaveni':
      datSplat = datVyst;
      datSplat.setDate(datVyst.getDate()+nSplatnost);
      jQ("#datum_splatnosti").datepicker('setDate',datSplat);
    break;
    case 'datum_splatnosti':
      datVyst = datSplat;
      datVyst.setDate(datSplat.getDate()-nSplatnost);
      jQ("#datum_vystaveni").datepicker('setDate',datVyst);
    break;
  }
  
  jQ("#datum_zdan_plneni").attr('value',jQ("#datum_vystaveni").attr('value'));      
  jQ("#tr_datum_zdan_plneni td span:first").text(jQ("#datum_vystaveni").attr('value'));
  
  //jQ("#splatnost span:first").text(nSplatnost);
  jQ("#splatnost input[name='splatnost']").attr('value',nSplatnost);
  
  bChangePlatba = true;
}

function removeErrorMsg(inpEle)
{
  jQ(inpEle).removeClass('error');
  jQ(inpEle).parent().children("span[class='error_msg'],div[class='error_msg']").remove();
}

/**
 * zobrazi chybovou hlasku pro urcity input
 * chybove hlasky se berou dle atributu NAME formularoveho pole v poli chybovych hlasek 'oItemError'  
 * @inpEle - HTML DOM element(input), ke kteremu patri chybova hlaska
 */   
function addErrorMsg(inpEle)
{
  jQ(inpEle).addClass('error');
  var inpName = jQ(inpEle).attr('name');
  
  if(jQ(inpEle).parent().children("span[class='error_msg'],div[class='error_msg']").length == 0)  
    jQ(inpEle).parent().append(htmlErrorMsgBlockStart + oItemError[inpName] + htmlErrorMsgBlockEnd);
}

function setDphVisibility()
{
  /// faktura s DPH ///
  if(jQ("input[name='dod_dph']").attr('checked')){
    bCountWithDph = true;
    jQ("#list_of_items td.dph_hid,#list_of_items th.dph_hid,#list_of_complete tr.dph_hid").show();
  }else{
    bCountWithDph = false;
    jQ("#list_of_items td.dph_hid,#list_of_items th.dph_hid,#list_of_complete tr.dph_hid").hide();
  }
  
  /// prepocitani polozek ///
  jQ("#list_of_items tr[id^='item']").each(function(){
    recountItem(jQ(this).attr('id'));
  });
  recountItems();  
}
