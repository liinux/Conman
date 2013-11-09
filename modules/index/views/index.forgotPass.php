<div style="margin-bottom: 20px;">
<?php if($status == 'emailsent'):?>
Ett mail har skickats till adressen <?php echo $email;?>, klicka på länken i mailet för att fortsätta.<br>
<br>
Har du inte fått något mail (kolla även i din spam-/skräppost)?<br>
Kontakta <a href="mailto:<?php echo Settings::$TechEmail; ?>"><?php echo Settings::$TechEmail; ?></a>!
<?php elseif($status == 'mailing_error'):?>
Ett fel uppstod när vi försökte maila dig.<br>
<br>
Kontakta <a href="<?php echo Settings::$TechEmail; ?>"><?php echo Settings::$TechEmail; ?></a>!
<?php elseif($status == 'wrong_email'):?>
Tyvärr är adressen du skrev in inte i våran databas, antingen är du inte registrerad eller så skrev du in fel adress.<br>
<br>
Vänligen <a href="<?php echo Router::url('forgetPass');?>">försök igen</a> eller <a href="<?php echo Router::url('index');?>">registrera dig</a>!
<?php endif;?>
</div>
