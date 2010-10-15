<div class="header">
       <ul class="nav">
          <li><a href="">Nápověda</a></li>
          <li><a href="">FAQ</a></li>
          <li><a href="" class="last">Kontakt</a></li>
       </ul>
       
		<?php if($main->superadminlinks && count($main->superadminlinks)) : ?> 
			<ul class="nav superadmin">
			<?php foreach($main->superadminlinks as $k=>$link) : ?>
       			<li><a <?php if($k==count($main->superadminlinks)-1):?> class="last" <?php endif;?>href="<?php print $link->href;?>"><?php print $link->text;?></a></li>
       		<?php endforeach; ?>
       		</ul>
       	<?php endif; ?>
       
       <div class="clear"></div>
       <a href="<?php print getUrl();?>"><img class="logo" src="<?php print getImgUrl("main_logo.gif");?>" alt="Návrat na úvodní stránku" /></a>
       <div class="clear"></div>
       <div class="zalozky">
          <?php if($main->zalozky) : ?> 
			<?php foreach($main->zalozky as $link) : ?>
       			<a href="<?php print $link->href;?>" <?php if($link->active):?>class="active"<?php endif;?>><?php print $link->menutitle;?></a>
       		<?php endforeach; ?>
       		<div class="clear"></div>
       	<?php endif; ?>          
       </div>
</div>
<div class="obsah">
	<div class="right_column">
        <h2>Uživatelský panel</h2>          
          <a class="nastaveni" href="<?php print $main->nastaveni->href;?>">Nastavení</a>
          <div class="clear"></div>
          <div class="panel">
          	<div class="login">
            <?php if($main->login->authenticated == true):?>
              <p>Přihlášen: <strong><?php print $main->user->name." ".$main->user->surname;?></strong><br />
              Dnes je: <strong>25.04.2010</strong> Svátek má: <strong>Milan</strong><br />                        
              Vystavené faktury za: <strong>500 000,- CZK</strong><br />
              Neuhrazené faktury po splatnosti: <strong>250 000,- CZK</strong></p>
              <a href="<?php print $main->login->href;?>" title="Odhlásit">Odhlásit</a>
            <?php else:?>
            	<a href="<?php print $main->login->href;?>" title="Přihlásit">Přihlásit</a>
            <?php endif;?>
            </div>
          </div>
          <h2>Uživatelský panel</h2> 
          <p>Nejake tagy</p>
          <h2>
            Odkazy
          </h2>
          <p class="akt"><a href="http://mail.seznam.cz" target="_blank">Email</a></p>
          <p class="akt"><a href="https://login.mojedatovaschranka.cz" target="_blank">Datová schránka</a></p>
          <p class="akt"><a href="http://portal.justice.cz/justice2/uvod/uvod.aspx" target="_blank">Obchodní rejstřík</a></p>
          <p class="akt"><a href="http://www.firmy.cz/" target="_blank">Firmy.cz</a></p>
	</div>
	<div class="left_column">
		<!-- div id="js_missing">Tyto stránky vyžadují zapnutý javascript!</div-->
    	<?php print $sub_content; ?>
	</div>
        
	<div class="clear"></div>
</div>

<div class="footer">
	<ul class="nav">
    	<li><a href="#" class="last">Kontakt</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Nápověda</a></li>
    </ul>
    <div class="clear"></div>
    <div class="awete">
            Design by: <a href="http://awete.cz" target="_blank"><img src="img/awt.gif" alt="AWETE" /></a>
    </div>
</div>    