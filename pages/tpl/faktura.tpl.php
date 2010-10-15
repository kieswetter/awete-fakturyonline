<?php if($faktura->errors) :
		foreach($faktura->errors as $err) :?>
			<div class="error"><?php print $err;?></div>		
<?php endforeach;
endif;?>
<?php if($faktura->alerts) :
		foreach($faktura->alerts as $err) :?>
			<div class="alert"><?php print $err;?></div>		
<?php endforeach;
endif;?>
	
<form action="" class="faktura" name="fc_faktura" method="post">
    <input type="hidden" name="fc_faktura_save" value="false" />
    <div class="faktura">      
      <div class="f_header">
        <div class="kontakty">
            IČO: 25115804<br />
            DIČ: CZ25115804,<br />
            Městský soud v Praze, Spis. zn.: C.51029<br />
            tel.: +420 234 262 000 fax: +420 234 262 009<br />
            e-mail: <a href="mailto:faktury@active24.cz">faktury@active24.cz</a>
        </div>
        <div class="adresa">
            <strong>ACTIVE 24, s.r.o.</strong><br />
            Sokolovská 394/17<br />
            186 00 Praha 8 - Karlín,<br />
            Okres: Hlavní město Praha
        </div>    
        <img class="logo" src="<?php print getImgUrl("logo.gif");?>" alt="Logo 250px/100px" />
        <div class="clear"></div>
      </div>
      
      <h1>Fakrura <span>č. 123465789</span></h1> 
      
		<div class="inbox">     
				<div class="odberatel">
		          <h2><?php print getString('Odběratel','main');?>
		          	<?php if(isset($faktura->odberatele) && count($faktura->odberatele)) :?>
		        		<select id="odb_id" name="odb_id">
		        			<option value="null">vyberte</option>
		        			<?php foreach($faktura->odberatele as $item):?>
		        				<option value="<?php print $item['id']?>"><?php print $item['nazev']?></option>
		        			<?php endforeach;?>
		        		</select>
		        		<script type="text/javascript">
		        			var aOdberatele = new Object();
		        			var id;
		        			<?php foreach($faktura->odberatele as $item) :?>
								id = <?php print $item['id']?>;
	        					aOdberatele[id] = new Array();
		        				aOdberatele[id]['nazev'] = '<?php print $item['nazev']?>';
		        				aOdberatele[id]['ulice'] = '<?php print $item['ulice']?>';
		        				aOdberatele[id]['mesto'] = '<?php print $item['mesto']?>';
		        				aOdberatele[id]['psc'] = '<?php print $item['psc']?>';
		        				aOdberatele[id]['ico'] = '<?php print $item['ico']?>';
		        				aOdberatele[id]['dic'] = '<?php print $item['dic']?>';
		        				aOdberatele[id]['email'] = '<?php print $item['email']?>';
		        				aOdberatele[id]['tel'] = '<?php print $item['tel']?>';
		        				aOdberatele[id]['id'] = '<?php print $item['id']?>';
		        			<?php endforeach;?>
		        		</script>
		        	<?php endif;?>
		          </h2>
		          	<?php if($faktura->form_errors->odb_id):?>
		        		<span class="error_msg">
		        			<?php if($faktura->form_errors->odb_id['sent']) print getString("Nebyly zaslány údaje o dodavateli!",'faktura');?>
		        			<?php print implode("<br />",$faktura->form_errors->odb_id['msg'])?>
		        		</span>
		        	<?php endif;?>        
		          <table>
		            <tr>
		              <th><span class="req">*</span><?php print getString('Název','faktura');?></th>
		              <td>
		              	<input type="text" name="odb_nazev" value="<?php print $faktura->data->odb_nazev?>" />
		              	<?php if($faktura->form_errors->odb_nazev):?><span class="error_msg"><?php print implode("<br />",$faktura->form_errors->odb_nazev['msg'])?></span><?php endif;?>
		              </td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span><?php print getString('Ulice','faktura');?></th>
		              <td>
		              	<input type="text" name="odb_ulice" value="">
		              	<?php if($faktura->form_errors->odb_ulice):?><span class="error_msg"><?php print implode("<br />",$faktura->form_errors->odb_ulice['msg'])?></span><?php endif;?>
		            	</td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span><?php print getString('Město','faktura');?></th>
		              <td><input type="text" name="odb_mesto" value=""></td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span><?php print getString('PSČ','faktura');?></th>
		              <td><input type="text" name="odb_psc" value=""></td>
		            </tr>
		            <tr>
		              <th><?php print getString('IČO','faktura');?></th>
		              <td><input type="text" name="odb_ico" value=""></td>
		            </tr>
		            <tr>
		              <th><?php print getString('DIČ','faktura');?></th>
		              <td><input type="text" name="odb_dic" value=""></td>
		            </tr>
		            <tr>
		              <th><?php print getString('Mail','faktura');?></th>
		              <td><input type="text" name="odb_email" value=""></td>
		            </tr>
		            <tr>
		              <th><?php print getString('Tel.','faktura');?></th>
		              <td><input type="text" name="odb_tel" value=""></td>
		            </tr>
		        </table>
		        </div>  
		        <div class="dodavatel">
		        <h2>
		        	Dodavatel
		        	<?php if(isset($faktura->dodavatele)) : 
		        		if(count($faktura->dodavatele) > 1) :?>
			        		<select name="dod_id">
			        			<?php foreach($faktura->dodavatele as $dod):?>
			        				<option value="<?php print $dod['id']?>"><?php print $dod['nazev']?></option>
			        			<?php endforeach;?>
			        		</select>
			        		<script type="text/javascript">
			        			var aDodavatele = new Object();
			        			var id;
			        			<?php foreach($faktura->dodavatele as $item) :?>
									id = <?php print $item['id']?>;
		        					aDodavatele[id] = new Array();
			        				aDodavatele[id]['nazev'] = '<?php print $item['nazev']?>';
			        				aDodavatele[id]['ulice'] = '<?php print $item['ulice']?>';
			        				aDodavatele[id]['cislo'] = '<?php print $item['ulice']?>';
			        				aDodavatele[id]['mesto'] = '<?php print $item['mesto']?>';
			        				aDodavatele[id]['psc'] = '<?php print $item['psc']?>';
			        				aDodavatele[id]['ucet'] = '<?php print $item['ucet']?>';
			        				aDodavatele[id]['bankod'] = '<?php print $item['bankode']?>';
			        				aDodavatele[id]['ico'] = '<?php print $item['ico']?>';
			        				aDodavatele[id]['dic'] = '<?php print $item['dic']?>';
			        				aDodavatele[id]['web'] = '<?php print $item['web']?>';
			        				aDodavatele[id]['email'] = '<?php print $item['email']?>';
			        				aDodavatele[id]['tel'] = '<?php print $item['tel']?>';
			        				aDodavatele[id]['mobil'] = '<?php print $item['mobil']?>';
			        				aDodavatele[id]['fax'] = '<?php print $item['fax']?>';
			        				aDodavatele[id]['soud'] = '<?php print $item['soud']?>';
			        				aDodavatele[id]['spis_zn'] = '<?php print $item['spis_zn']?>';
			        				aDodavatele[id]['dph'] = <?php print $item['platce_dph']?>;
			        				aDodavatele[id]['id'] = <?php print $item['id']?>;
			        			<?php endforeach;?>
		        			</script>
			        	<?php else:?>
			        		<input type="hidden" name="dod_id" value="<?php print $faktura->data->dod_id?>" />	
			        	<?php endif;?>
			        <?php endif;?>
		        </h2>
		        <?php if($faktura->form_errors->dod_id):?>
		        	<span class="error_msg">
		        		<?php if($faktura->form_errors->dod_id['sent']) print getString("Nebyly zaslány údaje o dodavateli!",'faktura');?>
		        		<?php print implode("<br />",$faktura->form_errors->dod_id['msg'])?>
		        	</span>
		        <?php endif;?>
		       
		        <?php if(isset($faktura->data->dod_id) && isset($faktura->dodavatele[$faktura->data->dod_id])) :
		        	$dod = $faktura->dodavatele[$faktura->data->dod_id];
		        ?>
		        	<table>
			            <tr>
			              <th><span class="req">*</span>Název</th>
			              <td><?php print $dod['nazev']?></td>
			            </tr>
			            <tr>
			              <th><span class="req">*</span>Ulice</th>
			              <td><?php print $dod['ulice']?></td>
			            </tr>
			            <tr>
			              <th><span class="req">*</span>Město</th>
			              <td><?php print $dod['mesto']?></td>
			            </tr>
			            <tr>
			              <th><span class="req">*</span>PSČ</th>
			              <td><?php print $dod['psc']?></td>
			            </tr>
			            <tr>
			              <th><span class="req">*</span>IČO</th>
			              <td><?php print $dod['ico']?></td>
			            </tr>
			            <tr>
			              <th>DIČ</th>
			              <td><?php print $dod['dic']?></td>
			            </tr>
			        </table>
			        <table>
			            <tr>
			              <th>Městský soud v</th>
			              <td><?php print $dod['soud']?></td>
			            </tr>
			            <tr>
			              <th>, Spis. zn.:</th>
			              <td><?php print $dod['spis_zn']?></td>
			            </tr>
			        </table>
			        <table>
			            <tr>
			              <th>Web</th>
			              <td><?php print $dod['web']?></td>
			            </tr>
			            <tr>
			              <th>E-mail</th>
			              <td><?php print $dod['email']?></td>
			            </tr>
			            <tr>
			              <th>Tel.</th>
			              <td><?php print $dod['tel']?></td>
			            </tr>
			            <tr>
			              <th>Fax</th>
			              <td><?php print $dod['fax']?></td>
			            </tr>
			            <tr>
			             <th><span class="req">*</span>Plátce DPH</th>
			             <td>
			             <?php 
			             if($dod['platce_dph'] == CD_FAKT_PLATCE_DPH_ANO) {
			             	print getString('Ano','faktura');
			             }else{
			             	print getString('Ne','faktura');			             		 
			             }?>
			             </td>
			            </tr>
			        </table>
		        <?php else:?>  
		          <table>
		            <tr>
		              <th><span class="req">*</span>Název</th>
		              <td><input type="text" name="dod_nazev"></td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span>Ulice</th>
		              <td><input type="text" name="dod_ulice"></td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span>Město</th>
		              <td><input type="text" name="dod_mesto"></td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span>PSČ</th>
		              <td><input type="text" name="dod_psc"></td>
		            </tr>
		            <tr>
		              <th><span class="req">*</span>IČO</th>
		              <td><input type="text" name="dod_ico"></td>
		            </tr>
		            <tr>
		              <th>DIČ</th>
		              <td><input type="text" name="dod_dic"></td>
		            </tr>
		        </table>
		        <table>
		            <tr>
		              <th>Městský soud v</th>
		              <td><input class="dod_soud_misto" name="dod_soud_misto" type="text"></td>
		            </tr>
		            <tr>
		              <th>, Spis. zn.:</th>
		              <td><input class="dod_soud_spis" name="dod_soud_spis" type="text">.</td>
		            </tr>
		        </table>
		        <table>
		            <tr>
		              <th>Web</th>
		              <td><input type="text" name="dod_web"></td>
		            </tr>
		            <tr>
		              <th>E-mail</th>
		              <td><input type="text" name="dod_email"></td>
		            </tr>
		            <tr>
		              <th>Tel.</th>
		              <td><input type="text" name="dod_tel"></td>
		            </tr>
		            <tr>
		              <th>Fax</th>
		              <td><input type="text" name="dod_fax"></td>
		            </tr>
		            <tr>
		             <th><span class="req">*</span>Plátce DPH</th>
		             <td><input class="radio" type="radio" name="dod_dph" value="<?php print CD_FAKT_PLATCE_DPH_ANO?>" /> <?php getString('Ano','faktura');?>
		              	 <input class="radio" type="radio" name="dod_dph" value="<?php print CD_FAKT_PLATCE_DPH_NE?>" checked="checked"> <?php getString('Ne','faktura');?>
		             </td>
		            </tr>
		        </table>
		        <?php endif;?>
		        </div>
		      	
		        <div class="info_splatnost">
		          <h2>Způsob úhrady</h2>
		          <table>
		            <tr>
		              <th>Datum vystavení <span class="note">formát: 25.3.2010</span></th>
		              <td>
		                <input type="text" name="datum_vystaveni" id="datum_vystaveni" value="<?php print $faktura->data->datum_vystaveni;?>" />
		              </td>
		            </tr>
		            <tr>
		              <th>Splatnost</th>
		              <td id="splatnost">              
		                <input type="text" name="splatnost" value="<?php print $faktura->data->splatnost;?>" />              
		                <span> dní</span>
		              </td>            
		            </tr>
		            <tr>
		              <th>Datum splatnosti</th>
		              <td>
		                <input type="text" name="datum_splatnosti" id="datum_splatnosti" value="<?php print $faktura->data->datum_splatnosti;?>" />
		                <span class="unlock" id="lockDatumSplatnosti">zamknout</span>
		              </td>            
		            </tr>
		            <tr id="tr_datum_zdan_plneni">
		              <th>Datum zdanitelného plnění</th>
		              <td id="td_datum_zdan_plneni">              
		                &nbsp;
		              </td>
		            </tr>
		            <tr>
		              <th>Způsob úhrady</th>
		              <td>
		                <select name="zpusob_uhrady">
		                  <option value="<?php print CD_FAKT_UHRADA_HOTO?>"><?php print getString(CD_FAKT_UHRADA_HOTO_STRING, 'faktura')?></option>
		                  <option value="<?php print CD_FAKT_UHRADA_PREV?>"><?php print getString(CD_FAKT_UHRADA_PREV_STRING)?></option>
		                </select>
		              </td>
		            </tr>
		            <tr>
		              <th>Číslo faktury</th>
		              <td><input type="text" name="cislo_faktury" value="0"></td>
		            </tr>
		            <tr>
		              <th>Variabilní symbol</th>
		              <td><input type="text" name="variabilni_symbol" value="0"></td>
		            </tr>
		          </table>
		        </div>
		      
		      
		      <div class="clear"></div>
		      
		      <input type="hidden" name="typ_faktury" value="<?php print $faktura->data->typ_faktury;?>" />
		      
		      <div class="info_fakturace">
		        <h2>Položky</h2>
		        <table id="list_of_items">
		          <tr>
		            <th class="cislo">č. pol.</th>
		            <th class="popis">Popis položky</th>
		            <th class="pocet">Počet j.</th>
		            <th class="cena">Cena / j.</th>
		            <th style="display: none;" class="dph_hid">DPH / pol.</th>
		            <th style="display: none;" class="dph_hid">Cena / pol. celkem bez DPH</th>
		            <th class="cena_pol">Cena / pol. celkem</th>
		            <th class="icons"></th>
		          </tr>
		          <tr>            
		            <td colspan="5"></td>
		            <td class="dph_hid"></td>
		            <td class="dph_hid"></td>
		            <td class="icons"><a href="#" id="add_item" title="Přidat položku"><span>přidat položku</span></a></td>
		          </tr>          
		        </table>        
		        
		        <table id="list_of_complete">  
		          <tr class="dph_hid">
		            <th>Součet DPH 10%</th>
		            <td id="complete_dph10">
		              <input type="hidden" name="complete_dph10">
		              <span>0</span>
		              <span>Kč</span>
		            </td>
		          </tr>
		          <tr class="dph_hid">
		            <th>Součet DPH 20%</th>
		            <td id="complete_dph20">
		              <input type="hidden" name="complete_dph20">
		              <span>0</span>
		              <span>Kč</span>
		            </td>
		          </tr>
		          <tr class="dph_hid">
		            <th>DPH celkem</th>
		            <td id="complete_dph">
		              <input type="hidden" name="complete_dph">
		              <span>0</span>
		              <span>Kč</span>
		            </td>
		          </tr>
		
		          <tr class="dph_hid">
		            <th>Celková cena bez DPH</th>
		            <td id="complete_cena">
		              <input type="hidden" name="complete_cena">
		              <span>0</span>
		              <span>Kč</span>
		            </td>
		          </tr>
		          <tr>
		            <th>Celková cena</th>
		            <td id="complete_cenadph">
		              <input type="hidden" name="complete_cenadph">
		              <span>2000</span>
		              <span>Kč</span>
		            </td>
		          </tr>
		       </table>
	      	</div>
	      
	       	<p>Vystavil: <input type="text" name="vystavil">, tel.: <input type="text" name="vystavil_tel"> </p>
	      
    	</div>
    
	    <div class="buttony" id="fak_buttony_save">
			<?php foreach($faktura->submits as $but) :?>
	      		<?php if($but['type'] == 'submit') :?>
	      			<input type="submit" id="<?php print $but['id']?>" name="<?php print $but['name']?>" value="<?php print $but['value']?>" />
	      		<?php else: ?>
	      			<button id="<?php print $but['id']?>"><?php print $but['value']?></button>
	      		<?php endif;?>
	      	<?php endforeach;?>
	    </div>
    </div>
</form>