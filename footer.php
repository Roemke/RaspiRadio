<?php
/* do it as php include because the footer vanishs on mobile device if I click in an empty
  area on page 1 - don't understand */
echo <<< eofoot
 <!-- external toolbar, remains on all pages, but auto-init does not
    work - we have to do it by ourself -->
    <div data-role="footer" data-position='fixed'>
      <div data-role="navbar">
      <ul>
        <li>
          <a data-role="button"   data-transition="slideup"	href='#aktuellPage'>Aktuell</a>
        </li>
        <li>
          <a data-role="button"   data-transition="slideup" href='#stationsPage'>Sender</a>
        </li>
        <li>
          <a data-role="button"   data-transition="slideup" href='#helpPage'>Hilfe</a>
        </li>
      </ul>
     </div><!-- navbar -->
     </div><!-- footer -->
eofoot;
?>