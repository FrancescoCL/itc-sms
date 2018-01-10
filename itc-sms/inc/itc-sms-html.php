<?php
/** ********************************* **/
/**       HTML OUTPUT FUNCTIONS       **/
/**  Author: Francesco Casadei Lelli  **/
/** ********************************* **/



function html_new_user(){
	?>
	<div class='wrap'>
        <div class="field-container">
            <h1>Registrazione nuovo utente
                <a href='?page=itc-sms-menu' class='page-title-action'>Torna alla lista</a>
            </h1>
            <h3 id="user_saved" style="display: none">Utente registrato</h3>
            <h3 id="email_error" style="display: none">Email non valida</h3>
            <h3>Nome:</h3>
            <input id="itc_sms_user_name" type="text" placeholder="" maxlength="30" autofocus/>
            <h3>Cognome:</h3>
            <input id="itc_sms_user_surname" type="text" placeholder="" maxlength="30"/>
            <h3>Numero di telefono:</h3>
            <input id="itc_sms_user_telephone" type="text" placeholder="" maxlength="13"/>
            <h3>Email (opzionale):</h3>
            <input id="itc_sms_user_email" type="text" placeholder="" maxlength="30"/><br><br>
            <input id="itc_register_user" type="button" value="Registra utente"/><br><br>
        </div>
    </div>
	<?php
}

function html_table_intestation(){
	?>
    <head><meta charset="UTF-8"></head>
    <div class='wrap'>
        <div id='display_user'>
            <h1>Iscritti alla newsletter
                <a class='page-title-action' href='?page=itc-sms-new-subscriber'>Nuovo iscritto</a>
                <a href='?page=itc-sms-export' class='page-title-action'>Esporta</a>
                <a href='?page=itc-sms-import' class='page-title-action'>Importa</a>
            </h1>
			<!-- TABLE GOES HERE -->
    <?php
}

function html_modify_module(){
	?>
		<!-- DISPLAY USER DIV CLOSING -->
		</div>
		<div id='modify_user' class='field-container' style='display: none'>
			<h1>Modifica dati iscrizione
				<a href='?page=itc-sms-menu' class='page-title-action'>Torna alla lista</a>
			</h1>
			<h3 id='user_mod' style='display: none'>Iscritto aggiornato</h3>
			<h3 id='mod_email_error' style='display: none'>Email non valida</h3>
			<input id='user_id' type='text' style='display: none' placeholder=''/>
	
			<h3>Nome:</h3>
			<input id='itc_sms_mod_name' type='text' placeholder='' maxlength='30'/>
			<h3>Cognome:</h3>
			<input id='itc_sms_mod_surname' type='text' placeholder='' maxlength='30'/>
			<h3>Numero di telefono:</h3>
			<input id='itc_sms_mod_telephone' type='text' placeholder='' maxlength='30'/>
			<h3>Email (opzionale):</h3>
			<input id='itc_sms_mod_email' type='text' placeholder='' maxlength='30'/><br><br>
			<input id='itc_modify_user' type='button' value='Modifica iscritto'/><br><br>
		</div>
	<!-- WRAP DIV CLOSING -->
	</div>
	<?php
}

function html_export_user($csv_name){
	?>
    <div class='wrap'>
        <h1>Esporta dati iscritti
            <a href='?page=itc-sms-menu' class='page-title-action'>Torna alla lista</a>
        </h1>
        <h3 id="export_status" style="display: none">Iscritti esportati</h3>
        <h3 id="export_error" style="display: none">Errore durante l'esportazione dei dati</h3>
        <br>
        <p>Esporta anche gli utenti non più iscritti: &nbsp &nbsp &nbsp</p>
        <form id="radio_in">
            Sì <input type="radio" name="yn" value="0"/> &nbsp
            No <input type="radio" name="yn" value="1" checked/>
        </form>
        <br>
        <a href="<?php echo $csv_name ?>" download id="file" style="display: none"></a>
        <input type="button" id="start_export" value="Esporta"/>
        <br><br>
        <h2>Note per l'utilizzo del file esterno:</h2>
        <u1 style="list-style-type: disc">
            <li><strong>NON</strong> modificare il campo ID.</li>
            <li>Non è possibile aggiungere nuovi utenti dal file esportato, utilizzare l'apposito modulo.</li>
            <li>Nel campo "Iscritto" inserire solo <strong>si</strong> o <strong>no</strong>, senza usare caratteri speciali o maiuscole.</li>
            <li>Il file esportato viene salvato nella directory predefinita per i download.</li>
        </u1>
    </div>
    <?php
}

function html_import_user($csv_name){
	?>
    <div class='wrap'>
        <h1>Importa dati iscritti
            <a href='?page=itc-sms-menu' class='page-title-action'>Torna alla lista</a>
        </h1>
        <br>

        <h3 id="import_status" style="display: none">Iscritti importati</h3>
        <h3 id="import_error" style="display: none">Errore durante l'importazione dei dati</h3>
        <br>
        <form enctype="multipart/form-data">
            <input name="file" type="file" id="fileUpload" accept=".csv" />
            <br><br>
            <input type="button" id="start_import" value="Importa" />
        </form>
        <br>
        <h2>Note per l'utilizzo del file esterno:</h2>
        <u1>
            <li>Utilizzare solo i file esportati tramite questo plugin, con nome nel seguente formato: <strong><?php echo $csv_name ?></strong></li>
            <li>Dopo l'esportazione il file viene salvato nella directory predefinita per i download.</li>
        </u1>
    </div>
    <?php
}