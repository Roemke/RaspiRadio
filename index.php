<!DOCTYPE html>
<head>
	<!-- page must be copied to a webserver -->
	<!-- Basic Page Needs
	================================================== -->
	<meta charset="utf-8">
	<title>Mobile Radio Interface</title>
	<meta name="description" content="simple Radio Interface for MPD">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes" >
        <meta name="apple-mobile-web-app-status-bar-style" content="black" >
        <link rel="apple-touch-icon" href="data/radio.png"/>
        <link rel="apple-touch-icon-precomposed" href="data/radio.png"/>
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="shortcut icon" sizes="196x196" href="data/radio.png">
        <link rel="shortcut icon" sizes="128x128" href="data/radio.png">
        <link rel="shortcut icon" href="data/radio.png">

	<!-- Mobile Specific Metas
	================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <!-- style sheet -->
	<link rel="stylesheet" type="text/css" href="css/jquery.mobile-1.4.5.css">
	<link rel="stylesheet" type="text/css" href="css/own.css">
	<!-- nur jquery selbst-->
	<script src="js/jquery-2.1.1.min.js" type="text/javascript"></script>
	<!-- eigene Javascripts -->
	<script src="js/mobileInterface.js"></script>  

	<!-- notiz (da schon 2 mal vergessen )  sollte man mobileinit verwenden wollen, muss man vor dem
	  Einbinden von jquery mobile an den event binden --> 
	<!-- und jquery mobile js  als letztes --> 
	<script src="js/jquery.mobile-1.4.5.js" type="text/javascript"></script>
	<script>
	/*$(function(){
		$( " [data-role='footer']" ).toolbar({ theme: "a" });
	});
  	external toolbar does not work perfect, on first page it vanishes if I
	clic in empty field on mobile chrome
	*/
	</script>
</head>
<body>
	<!-- Primary Page Layout
	================================================== -->
	<div data-role="page" id="aktuellPage">
		<div data-role="header">
			<h1>Aktuell</h1>
		</div> <!-- header -->
		<div data-role="content">
      <div class='framed'>
			  <div data-role="fieldcontain">
   			 <label for="volSlider">Volume (<span id='volNumber'>25</span>%):</label>
      			 <input type="range" name="volSlider" id="volSlider" value="25" min="0"
      			max="100" data-highlight="true"  />
        </div><!--fieldcontain-->
        <div data-role="navbar" class="myControlButtons">
         <ul>
           <li><a id="bPlay" data-role="button" href='#'> Play </a></li>
           <li><a id="bStop" data-role="button" href='#'> Pause </a></li>
           <li><a id="bOff" data-role="button" href='#'> PI Off </a></li>
         </ul>
        </div><!--navbar-->
      </div> <!--framed -->
      <div class="framed">
        <div><h2> Name: </h2> <span id='name'> </span></div>
			  <div><h2> Title: </h2> <span id='title'> </span></div>
		  </div>
		</div><!-- data-role content -->
	<?php include("footer.php") ?>
	</div> <!-- page  ende -->
	
  <!-- ============= naechste Seite stations ===================== -->
  <!-- javascript fuer stations seite -->
	<div data-role="page" id='stationsPage'>
		<div data-role="header">
			<h1>Sender</h1>
		</div> <!-- header -->
		<div data-role="content">
			<!-- liste fuer Sender 	-->
			<ol data-role='listview' id='senderList' data-inset='true'>
			</ol>
		</div><!-- data-role content -->
		<?php include("footer.php") ?>
	</div> <!-- page  ende -->

  <!-- ============= naechste About ===================== -->
	<div data-role="page" id='helpPage'>
		<div data-role="header">
			<h1>&Uuml;ber / Hilfe </h1>
		</div> <!-- header -->
		<div data-role="content">
		<h1 style="display:inline-block">RaspiRadio</h1> 
		&ndash; Mobiles Interface zur Steuerung des MPD. 
		<p>Ich wollte einen momentan herum liegenden raspberry pi als
		Internetradio nutzen. Der MPD geh&ouml;rt fast zum Standard,
		aber ein Interface, dass sich nur auf die Bedienung als Radio konzentriert
		habe ich nicht gefunden. ympd ist sehr nett, aber unterst&uuml;tzt mir zur
		viel.</p>
		<p> Zur Steuerung gibt es nicht viel zu sagen, auf der
		Sender-Seite werden die Sender angezeigt, ein "Click"
		schaltet um und startet das Abspielen. </p>
		<p> 
		 Intern werden die Sender in einer sqlite-Datei vorgehalten,
		 die Playlists von MPD werden nicht verwendet. Ein einfaches 
		 Webinterface zum bef&uuml;llen der Senderliste findet sich unter
		 <a href="php/dbInterface.php" target="_blank">
		 http://&lt;raspi-ip&gt;/radio/dbInterface.php</a>. Die Bedienung
		 ohne Tastatur ist nicht empfehlenswert :-).</p>
		<p>Auf der Seite Aktuell werden Lautst&auml;rkeregler, aktueller Sender und 
		 drei Buttons zur Steuerung angezeigt. <br>
		 Der Button PI Off fährt den PI herunter. Um wieder zu starten: einmal Spannungsversorgung
		 ziehen und wieder einstecken. (Dazu muss in /etc/sudoers die Zeile www-data ALL=(ALL) NOPASSWD: /sbin/halt) 
		 vorhanden sein.</p>		  
		</div><!-- data-role content -->
	<?php include("footer.php") ?>
	</div> <!-- page  ende -->

 	<!-- End Document
	================================================== -->

</body>
</html>
