<?php
include_once('css-base.php');
?>

@import url('/css/fonts.css');

.container {
    width: 100%;
    text-align: left;
    margin: auto;
    background : <?php echo $backgroundContent; ?>;
}

body, font {
    font: 18px 'Source Sans Pro', sans-serif;
    -webkit-text-size-adjust: none;
    color: <?php echo $textContent; ?>;
    -moz-text-size-adjust: none;
    -ms-text-size-adjust: none;
    text-size-adjust: none;
}

.header {
    display: none;
    background : <?php echo $backgroundBanners; ?>;
    text-decoration : none;
    color : <?php echo $textBanners; ?>;
    font-family : 'Source Sans Pro', sans-serif;
    text-align : left;
    padding : 5px 0px 5px 0px;
 }

.header h1 {
   font-weight: 500;
}

.nav {
    display: none;
    float : left;
    margin : 0;
    padding : 3px 3px 3px 3px;
    width : 160px;
    background : <?php echo $backgroundNavPanel; ?>;
    font-weight : normal;
    min-height : 100%;
}

#hwInfo,
#radioInfo,
#pocsag-sec {
    display: none;
}

.content {
    padding : 5px 5px 5px 5px;
    color : <?php echo $textSections; ?>;
    background : <?php echo $backgroundContent; ?>;
    text-align: center;
    font-size: 1.4em;
}

.contentwide {
    padding: 5px 5px 5px 5px;
    color : <?php echo $textSections; ?>;
    background: <?php echo $backgroundContent; ?>;
    text-align: center;
    font-size: 1.4em;
}

.contentwide h2 {
    color: <?php echo $textSections; ?>;
    font: 1em 'Source Sans Pro', sans-serif;
    text-align: center;
    font-weight: bold;
    padding: 0px;
    margin: 0px;
}

.footer {
    background : <?php echo $backgroundBanners; ?>;
    text-decoration : none;
    color : <?php echo $textBanners; ?>;
    font-family : 'Source Sans Pro', sans-serif;
    font-size : 9px;
    text-align : center;
    padding : 10px 0 10px 0;
    clear: both;
}

#tail {
    height: 450px;
    width: 805px;
    overflow-y: scroll;
    overflow-x: scroll;
    color: #00ff00;
    background: #000000;
}

table {
    background: <?php echo $backgroundContent; ?>;
    color: <?php echo $textContent; ?>;
    vertical-align: middle;
    text-align: center;
    empty-cells: show;
    padding-left: 0px;
    padding-right: 0px;
    padding-top: 0px;
    padding-bottom: 0px;
    border-collapse:collapse;
    border-color: #000000;
    border-style: solid;
    border-spacing: 4px;
    border-width: 2px;
    text-decoration: none;
    font-family: 'Source Sans Pro', sans-serif;
    width: 100%;
    white-space: nowrap;
}

table th {
    font-family: 'Inconsolata', monospace;
    text-decoration: none;
    color : <?php echo $textBanners; ?>;
    background: <?php echo $backgroundBanners; ?>;
    border: 1px solid #c0c0c0;
}

table tr:nth-child(even) {
    background: <?php echo $tableRowEvenBg; ?>;
}

table tr:nth-child(odd) {
    background: <?php echo $tableRowOddBg; ?>;
}

table td {
    font-family: 'Inconsolata', monospace;
    text-decoration: none;
    border: 1px solid #000000;
    overflow-x: hidden;
    font-weight: 600;
}

body {
    background: <?php echo $backgroundPage; ?>;
    color: #000000;
}

a {
    text-decoration:none;
    
}

a:link, a:visited {
    text-decoration: none;
    color: <?php echo $textLinks; ?>
}

a.tooltip, a.tooltip:link, a.tooltip:visited, a.tooltip:active  {
    text-decoration: none;
    position: relative;
    color: #e9e9e9;
}

a.tooltip:hover {
    text-decoration: none;
    background: transparent;
}

a.tooltip span {
    text-decoration: none;
    display: none;
}

a.tooltip:hover span {
    text-decoration: none;
    display: block;
    position: absolute;
    top: 20px;
    left: 0; 
    z-index: 100;
    font: 16px 'Source Sans Pro', sans-serif;
    text-align: left;
    white-space: nowrap;
    background: #000000;
    opacity: 0.8;
    border: none;
    color: #e9e9e9;
    padding: 8px;
}

th:last-child a.tooltip:hover span {
    left: auto;
    right: 0;
}

a.tooltip span b {
    text-decoration: none;
    font: 16px 'Source Sans Pro', sans-serif;
    display: block;
    margin: 0;
    font-weight: bold;
    background: #000000;
    opacity: 0.9;
    border: none;
    color: #e9e9e9;
    padding: 4px 4px 3px 4px;
}

a.tooltip2, a.tooltip2:link, a.tooltip2:visited, a.tooltip2:active  {
    text-decoration: none;
    position: relative;
    font-weight: bold;
    color: #e9e9e9;
}

a.tooltip2:hover {
    text-decoration: none;
    color: #e9e9e9;
    background: transparent;
}

a.tooltip2 span {
    text-decoration: none;
    display: none;
}

ia.tooltip2:hover span {
    text-decoration: none;
    display: block;
    position: absolute;
    top: 20px;
    left: 0;
    width: 202px;
    z-index: 100;
    font: 16px 'Source Sans Pro', sans-serif;
    text-align: left;
    white-space: normal;
    background: #000000;
    opacity: 0.9;
    border: none;
    color: #e9e9e9;
    padding: 8px;
}

a.tooltip2 span b {
    text-decoration: none;
    font: 16px 'Source Sans Pro', sans-serif;
    display: block;
    margin: 0;
    font-weight: bold;
    border: none;
    color: #e9e9e9;
    padding: 0px;
}

ul {
    padding: 5px;
    margin: 10px 0;
    list-style: none;
    float: left;
}

ul li {
    float: left;
    display: inline; /*For ignore double margin in IE6*/
    margin: 0 10px;
}

ul li a {
    text-decoration: none;
    float:left;
    color: #999;
    cursor: pointer;
    font: 900 14px/22px "Arial", Helvetica, 'Source Sans Pro', sans-serif;
}

ul li a span {
    margin: 0 10px 0 -10px;
    padding: 1px 8px 5px 18px;
    position: relative; /*To fix IE6 problem (not displaying)*/
    float:left;
}

ul.mmenu li a.current, ul.mmenu li a:hover {
    background: url(/images/buttonbg.png) no-repeat top right;
    color: #0d5f83;
}

ul.mmenu li a.current span, ul.mmenu li a:hover span {
    background: url(/images/buttonbg.png) no-repeat top left;
}

h1 {
    text-align: center;
}

/* CSS Toggle Code here */
.toggle {
    position: absolute;
    margin-left: -9999px;
    z-index: 0;
}

.toggle + label {
    display: block;
    position: relative;
    cursor: pointer;
    outline: none;
}

input:disabled + label {
    color: #cccccc;
}

input.toggle-round-flat + label {
    padding: 1px;
    border: 1px solid transparent;
    width: 33px;
    height: 18px;
    background-color: #dddddd;
    border-radius: 10px;
    transition: background 0.4s;
}

input.toggle-round-flat + label:before,
input.toggle-round-flat + label:after {
    display: block;
    position: absolute;
    content: "";
}

input.toggle-round-flat + label:before {
    top: 1px;
    left: 1px;
    bottom: 1px;
    right: 1px;
    background-color: #fff;
    background: <?php echo $backgroundContent; ?>;
    border-radius: 10px;
    transition: background 0.4s;
}

input.toggle-round-flat + label:after {
    top: 2px;
    left: 2px;
    bottom: 2px;
    width: 16px;
    background-color: #dddddd;
    border-radius: 12px;
    transition: margin 0.4s, background 0.4s;
}

input.toggle-round-flat:checked + label {
    background-color: <?php echo $backgroundBanners; ?>;
}

input.toggle-round-flat:checked + label:after {
    margin-left: 14px;
    background-color: <?php echo $backgroundBanners; ?>;
}

input.toggle-round-flat:focus + label {
    box-shadow: 0 0 2px <?php echo $backgroundBanners; ?>;
    padding: 1px;
    border: 1px solid <?php echo $backgroundBanners; ?>;
    z-index: 5;
}

/* put the same color as in left vertical status */
.navbar {
    overflow: hidden;
    background-color: <?php echo $backgroundNavbar; ?>;
}

/* Links inside the navbar */
.navbar a {
    float: right;
    font-family : 'Source Sans Pro', sans-serif;
    font-size: 14px;
    color: <?php echo $textNavbar; ?>;
    text-align: center;
    padding: 5px 8px;
    text-decoration: none;
    -webkit-transition: all 0.25s ease-out;
    -moz-transition: all 0.25s ease-out;
    -ms-transition: all 0.25s ease-out;
    -o-transition: all 0.25s ease-out;
    transition: all 0.25s ease-out;
}

.dropdown .dropbutton {
    font-size: 14px;
    border: none;
    outline: none;
    color: <?php echo $textNavbar; ?>;
    padding: 5px 8px;
    background-color: <?php echo $backgroundNavbar; ?>;
    font-family: inherit;
    margin: 0;
}

.navbar a:hover, .dropdown:hover .dropbutton {
    color: <?php echo $textNavbarHover; ?>;
    background-color: <?php echo $backgroundNavbarHover; ?>;
}

 /* put the same color as in left vertical status */
.lnavbar {
    overflow: hidden;
    background-color: <?php echo $backgroundNavbar; ?>;
}

/* Expert menus */
.mainnav {
    display: inline-block;
    list-style: none;
    padding: 0;
    margin: 0 auto;
    width: 100%;
    background: <?php echo $backgroundNavbar; ?>;
    overflow: hidden;
}

.dropdown {
    position: absolute;
    top: 123px;
    width: 170px;
    opacity: 0;
    visibility: hidden;
}

.mainnav ul {
    padding: 0;
    list-style: none;
    -webkit-transition: all 0.25s ease-out;
    -moz-transition: all 0.25s ease-out;
    -ms-transition: all 0.25s ease-out;
    -o-transition: all 0.25s ease-out;
    transition: all 0.25s ease-out;
}

.mainnav li {
    display: block;
    float: left;
    font-size: 0;
    margin: 0;
    background: <?php echo $backgroundNavbar; ?>;
}

.mainnav li a {
    list-style: none;
    padding: 0;
    display: inline-block;
    padding: 1px 10px;
    font-family : 'Source Sans Pro', sans-serif;
    font-size: 14px;
    color: <?php echo $textNavbar; ?>;
    text-align: center;
    text-decoration: none;
}

.mainnav .has-subs a:after {
    content: "\f0d7";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-left: 1em;
}

.mainnav .has-subs .dropdown .subs a:after {
    content: "";
}

.mainnav li:hover {
    background: <?php echo $backgroundNavbarHover; ?>;
}

.mainnav li:hover a {
    color: <?php echo $textNavbarHover; ?>;
    background-color: <?php echo $backgroundNavbarHover; ?>;
}

/* First Level */
.subs {
    position: relative;
    width: 170px;
}

.has-subs:hover .dropdown,
.has-subs .has-subs:hover .dropdown {
    opacity: 1;
    visibility: visible;
}

.mainnav ul li,
.mainav ul li ul li  a {
    color: <?php echo $textDropdown; ?>;
    background-color: <?php echo $backgroundDropdown; ?>;
}

.mainnav li:hover ul a,
.mainnav li:hover ul li ul li a {
    color: <?php echo $textDropdown; ?>;
    background-color: <?php echo $backgroundDropdown; ?>;
}

.mainnav li ul li:hover,
.mainnav li ul li ul li:hover {
    background-color: <?php echo $backgroundDropdownHover; ?>;
}

.mainnav li ul li:hover a,
.mainnav li ul li ul li:hover a {
    color: <?php echo $textDropdownHover; ?>;
    background-color: <?php echo $backgroundDropdownHover; ?>;
}

.mainnav .has-subs .dropdown .has-subs a:after {
    content: "\f0da";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    position: absolute;
    top: 1px;
    right: 9px;
}

/* Second Level */
.has-subs .has-subs .dropdown .subs {
    position: relative;
    top: -133px;
    width: 170px;
    border-style: none none none solid;
    border-width: 1px;
    border-color: <?php echo $backgroundDropdownHover; ?>;
}

.has-subs .has-subs .dropdown .subs a:after {
    content:"";
}

.has-subs .has-subs .dropdown {
    position: absolute;
    width: 170px;
    left: 170px;
    opacity: 0;
    visibility: hidden;
}

.menuconfig .menuadmin .menudashboard .menuupdate .menuupgrade .menupower .menulogs .menubackup .menuexpert .menureset .menusysinfo {
    position: relative;
}

.menuconfig:before {
    content: "\f1de";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menuadmin:before {
    content: "\f2bd";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menuupdate:before {
    content: "\f021";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menuupgrade:before {
    content: "\f1b8";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menupower:before {
    content: "\f011";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menulogs:before {
    content: "\f06e";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menudashboard:before {
    content: "\f0e4";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menubackup:before {
    content: "\f187";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menuexpert:before {
    content: "\f0a3";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menureset:before {
    content: "\f1cd";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.menusysinfo:before {
    content: "\f05a";
    font-family: FontAwesome;
    font-style: normal;
    font-weight: normal;
    text-decoration: inherit;
    padding-right: 0.2em;
}

.disabled-service-cell {
    color: <?php echo $textModeCellDisabledColor; ?>;
    background: <?php echo $backgroundModeCellDisabledColor; ?>;
}

.active-service-cell {
    color: <?php echo $textServiceCellActiveColor; ?>;
    background: <?php echo $backgroundServiceCellActiveColor; ?>;
}

.inactive-service-cell {
    color: <?php echo $textServiceCellInactiveColor; ?>;
    background: <?php echo $backgroundServiceCellInactiveColor; ?>;
}

.disabled-mode-cell {
    color: <?php echo $textModeCellDisabledColor; ?>;
    padding:2px;
    text-align: center;
    border:0;
    background: <?php echo $backgroundModeCellDisabledColor; ?>;
}

.active-mode-cell {
    color: <?php echo $textModeCellActiveColor; ?>;
    border:0;
    text-align: center;
    padding:2px;
    background: <?php echo $backgroundModeCellActiveColor; ?>;
}

.inactive-mode-cell {
    color: <?php echo $textModeCellInactiveColor; ?>;
    border:0;
    text-align: center;
    padding:2px;
    background: <?php echo $backgroundModeCellInactiveColor; ?>;
}

paused-mode-cell {
    color: <?php echo $textModeCellActiveColor; ?>;
    border:0;
    text-align: center;
    padding:2px;
    background: <?php echo $backgroundModeCellPausedColor; ?>;
}

.paused-mode-span {
    background: <?php echo $backgroundModeCellPausedColor; ?>;
}

.error-state-cell {
    color: <?php echo $textModeCellInactiveColor; ?>;
    text-align: center;
    border:0;
    background: <?php echo $backgroundModeCellInactiveColor; ?>;
}

#lhTGN,
#lhCN,
#lhAc,
.noMob {
    display: none;
}

/*
.table-container {
    position: relative;
    overflow: auto;
    max-height: 255px;
}
*/

/* Tame Firefox Buttons */
/*
@-moz-document url-prefix() {
    select,
    input {
        margin : 0;
        padding : 0;
        border-width : 1px;
        font : 12px 'Source Sans Pro', sans-serif;
    }
    input[type="button"], button, input[type="submit"] {
        padding : 0px 3px 0px 3px;
        border-radius : 3px 3px 3px 3px;
        -moz-border-radius : 3px 3px 3px 3px;
    }
}
*/
