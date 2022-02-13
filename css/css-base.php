<?php
// Do not edit this file, instead use the new dashboard editor in the expert section.
// New CSS Overlay for Pi-Star, to allow setting the variables from outside this file.

// Output CSS and not plain text
header("Content-type: text/css");

// Default values
$backgroundPage = "#edf0f5";         // usually off-white
$backgroundContent = "#ffffff";      // The White background in the content section
$backgroundBanners = "#dd4b39";      // The ubiquitous Pi-Star Red
$backgroundNavbar = "#242d31";        // Navbar background color
$backgroundNavbarHover = "#a60000"; // Navbar hover color
$backgroundDropdown = "#f9f9f9";    // Dropdown menu background color
$backgroundDropdownHover = "#d0d0d0"; // Dropdown hover background color
$backgroundNavPanel = "#242d31";  // Background color of the nav panel
$backgroundServiceCellActiveColor = "#11DD11";
$backgroundServiceCellInactiveColor = "#BB5555";
$backgroundModeCellDisabledColor = "#606060";
$backgroundModeCellActiveColor = "#00BB00";
$backgroundModeCellInactiveColor = "#BB0000";
$backgroundModeCellPausedColor = "#ff9933";
$tableRowEvenBg = "#f7f7f7";		// Table Row BG Colour (Even)
$tableRowOddBg = "#d0d0d0";		// Table Row BG Colour (Odd)

$textBanners = "#ffffff";            // Usually white
$textNavbar = "#ffffff";           	// Navbar text color
$textNavbarHover = "#ffffff";       // Navbar hover color
$textDropdown = "#000000";          // Dropdown menu text color
$textDropdownHover = "#000000";     // Dropdown hover menu text color
$textTableHeaderColor = "#ffffff"; //add to default
$textServiceCellActiveColor = "#000000";
$textServiceCellInactiveColor = "#000000";
$textModeCellDisabledColor = "#b0b0b0";
$textModeCellActiveColor = "#000000";
$textModeCellInactiveColor = "#000000";

$textContent = "#000000";            // Used for the section titles
$textLinks = "#0000e0";

// extras
$fontSize = "18";  // Default font size used across most of the dashboard
$lastHeardRows = "40";

// Assign $value to $var only when it's set, otherwise use default value.
// That avoids messing when pistar-css.ini has changed.
function assignCSSValue(&$var, $value) {
    if (isset($value)) {
	$var = $value;
    }
}

// Check if the config file exists
if (file_exists('/etc/pistar-css.ini')) {
    // Use the values from the file
    $piStarCssFile = '/etc/pistar-css.ini';
    if (fopen($piStarCssFile,'r')) {
	$piStarCss = parse_ini_file($piStarCssFile, true);
    }
    
    // Set the Values from the config file
    assignCSSValue($backgroundPage, $piStarCss['Background']['PageColor']); // usually off-white
    assignCSSValue($backgroundContent, $piStarCss['Background']['ContentColor']); // The White background in the content section
    assignCSSValue($backgroundBanners, $piStarCss['Background']['BannersColor']); // The ubiquitous Pi-Star Red
    assignCSSValue($backgroundNavbar, $piStarCss['Background']['NavbarColor']); // Navbar background color
    assignCSSValue($backgroundNavbarHover, $piStarCss['Background']['NavbarHoverColor']); // Navbar hover color
    assignCSSValue($backgroundDropdown, $piStarCss['Background']['DropdownColor']); // Dropdown menu background color
    assignCSSValue($backgroundDropdownHover, $piStarCss['Background']['DropdownHoverColor']); // Dropdown hover background color
    assignCSSValue($backgroundNavPanel, $piStarCss['Background']['NavPanelColor']); // Background color of the nav panel

    assignCSSValue($backgroundServiceCellActiveColor, $piStarCss['Background']['ServiceCellActiveColor']);
    assignCSSValue($backgroundServiceCellInactiveColor, $piStarCss['Background']['ServiceCellInactiveColor']);
    assignCSSValue($backgroundModeCellDisabledColor, $piStarCss['Background']['ModeCellDisabledColor']);
    assignCSSValue($backgroundModeCellActiveColor, $piStarCss['Background']['ModeCellActiveColor']);
    assignCSSValue($backgroundModeCellInactiveColor, $piStarCss['Background']['ModeCellInactiveColor']);
    assignCSSValue($backgroundModeCellPausedColor, $piStarCss['Background']['ModeCellPausedColor']);
    
    assignCSSValue($tableRowEvenBg, $piStarCss['Background']['TableRowBgEvenColor']); // Table Row BG Colour (Even)
    assignCSSValue($tableRowOddBg, $piStarCss['Background']['TableRowBgOddColor']); // Table Row BG Colour (Odd)

    assignCSSValue($textContent, $piStarCss['Text']['TextColor']); // Used for the section titles
    assignCSSValue($textLinks, $piStarCss['Text']['TextLinkColor']); // Used for the hyperlinks
    assignCSSValue($textTableHeaderColor, $piStarCss['Text']['TableHeaderColor']);
    assignCSSValue($textBanners, $piStarCss['Text']['BannersColor']); // Usually white
    assignCSSValue($textNavbar, $piStarCss['Text']['NavbarColor']); // Navbar text color
    assignCSSValue($textNavbarHover, $piStarCss['Text']['NavbarHoverColor']); // Navbar hover color
    assignCSSValue($textDropdown, $piStarCss['Text']['DropdownColor']);	// Dropdown menu text color
    assignCSSValue($textDropdownHover, $piStarCss['Text']['DropdownHoverColor']); // Dropdown hover menu text color
    assignCSSValue($textServiceCellActiveColor, $piStarCss['Text']['ServiceCellActiveColor']);
    assignCSSValue($textServiceCellInactiveColor, $piStarCss['Text']['ServiceCellInactiveColor']);
    assignCSSValue($textModeCellDisabledColor, $piStarCss['Text']['ModeCellDisabledColor']);
    assignCSSValue($textModeCellActiveColor, $piStarCss['Text']['ModeCellActiveColor']);
    assignCSSValue($textModeCellInactiveColor, $piStarCss['Text']['ModeCellInactiveColor']);
    
    assignCSSValue($lastHeardRows, $piStarCss['ExtraSettings']['LastHeardRows']); // # of last heard rows to display
    assignCSSValue($fontSize, $piStarCss['ExtraSettings']['MainFontSize']); // Used for the main table font size
    assignCSSValue($headerFontSize, $piStarCss['ExtraSettings']['HeaderFontSize']); // Used for the header table font size
    assignCSSValue($tableBorderColor, $piStarCss['ExtraSettings']['TableBorderColor']); // Used for the table borders
    
}
?>
