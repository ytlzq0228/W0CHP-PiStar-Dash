## About `W0CHP-PiStar-Dash`, and Some Warnings

This is my *personal* and highly customized fork/version of the Pi-Star
dashboard, and related binaries.  I offer ZERO support. This is here for your
hacking and enjoyment, and you're fully on your own. *Use at your own risk*,
and DO NOT ask me for support.  Nor should you file/report any bugs etc. on any
official Pi-Star medium...this is not an official Pi-Star release.

It's *strongly* recommended that you read and heed the [rules and
caveats](https://w0chp.net/w0chp-pistar-dash/#rules).

Users of `W0CHP-PiStar-Dash` are on [Module **E** of
XLX493](https://w0chp.net/xlx493-reflector/). If you're lucky; and if I'm in a
good mood and/or if my availability allows, I can help on there.  Other users
on there are usually happy to help, too.  A very easy way to connect to the
support reflector, is via DMR; BrandMeister and/or TGIF Network, TalkGroup
`3170603`.

There are two methods of installation...

1. [Installation on an existing Pi-Star hotspot](#hotspot-installation)
2. [Installation via a disk image](#disk-image-installation)

### Installing `W0CHP-PiStar-Dash` on an Existing Pi-Star Hotspot {#hotspot-installation}

**Note: You need to have a Pi-Star hotspot running at least v4.1.6!**[^1]

1. Make a backup of your configuration if you wish -- just in case.

2. Open an SSH session to your Pi-Star instance.

3. Run this to familiarize yourself with the available options/arguments:[^2]

    ```text
    curl -Ls https://w0chp.net/WPSD-Install | sudo env NO_SELF_UPDATE=1 bash -s -- -h
    ```

    You will be presented with...


        [i] W0CHP PiStar-Dash Installer Command Usage:

          -h,   --help                  :  Display this help text.


          -id,  --install-dashboard     :  Install W0CHP dashboard.


          -idc  --install-dashboard-css :  Install W0CHP dashboard,
                                           WITH custom stylesheet.

          -rd,  --restore-dashboard     :  Restore original dashboard.


          -s,   --status                :  Display current install; original,
                                           or W0CHP installations.

4. When ready to install, run the above command again with the option/argument you wish...e.g:

    ```text
    curl -Ls https://w0chp.net/WPSD-Install | sudo env NO_SELF_UPDATE=1 bash -s -- -id
    ```

    (...to install the dashboard *without* the `W0CHP` custom CSS)

    **Important**: You *must* run the aforementioned commands with the exact syntax. Note the spaces and extra `--` (dashes), etc.
    Otherwise, the commands will fail.

5. When the installer completes, refresh your dashboard home page to see the changes.

## Updating `W0CHP-PiStar-Dash`

Once you install `W0CHP-PiStar-Dash`, it will automatically be kept up-to-date
with any new features/versions/etc. This is made possible via the native,
nightly Pi-Star updating process.[^3]

You can also manually invoke the update process via the dashboard admin section
(`Admin -> Update`), or by command line:

```text
sudo pistar-update
```

### Installing `W0CHP-PiStar-Dash` from a Bullseye-based Disk Image {#disk-image-installation}

Yes, you read that correctly; the `W0CHP-PiStar-Dash` disk image uses Bullseye
as the core operating system; far newer and better than the legacy Buster that
Pi-Star uses.

However, you had better be damn-well familiar working with `xz` decompression,
disk imaging, and setting up Pi-Star from scratch; because I will not cover any
of that here.

The Bullseye disk image is ready-to-go; with Pi-Star 4.1.6 and `W0CHP-PiStar-Dash` installed.

**Notes: This disk image is for Raspberry Pi-based platforms. You will need an
SD card of at least 4GB to install this disk image.**

* Disk Image Download: [<code>WPSD_Latest.img.xz</code>](https://w0chp.net/WPSD_Latest.img.xz) (664MB compressed; 2.3GB decompressed)
* MD5 Checksums: [<code>WPSD_Latest_MD5.txt</code>](https://w0chp.net/WPSD_Latest_MD5.txt)

## Uninstalling `W0CHP-PiStar-Dash`

Run:

```text
sudo WPSD-Installer -rd
```

...And the original Pi-Star Dashboard will be restored.

## Installation Demo Video

[See how easy it is to install...](https://w0chp.net/musings/wpsd-install-demo/)

## Features, Enhancements and Omissions (not an exhaustive list)

### Functionality Features

* Full M17 Protocol Support. ([See M17 Notes below...](#m17-notes))
* Full APRSGateway Support: Selectable APRS Data Sharing with specific modes.
* Full DGId Support.
* Selectable DMR Roaming Beacon Support: Network or Interval Mode (or disabled).
* "Live Caller" screen; similar to a "virtual Nextion screen"; displays current caller information in real-time.
* Current/Last Caller Details on Main Dashboard (name/location, when available).
* Talkgroup Names display in target fields (Brandmeister DMR, NXDN and P25 support only).
* YSF/NXDN/P25 link managers gives the ability to change links/rooms on-the-fly, rather than going through the large (and slow) configuration page.
* "XLX DMR Link Manager" allows fast switching of XLX reflectors and modules, as well as unlinking from modules to "pause" traffic.
* Searchable drop-downs for massive host lists in configuration/admin pages. E.g. D-Star Refs., YSF Hosts, XLX Hosts, DMR Hosts, etc.
* BrandMeister Manager revamps galore:
  * Now displays connected actual talk group names.
  * Connected dynamic talk groups now display idle-timeout time (due to no TX).
  * Added ability to mass-drop your static talk groups; and mass re-add the previously
    linked static talk groups.
  * Added ability to batch add/delete up to 10 static talk groups at a time.
* TGIF Manager; now displays connected actual talk group names. (**NOTE**: Since TGIF has moved to a new platform with no API available, this currently does not work until TGIF's API is made available.)
* "Instant Mode Manager" added to admin page; allows you to instantly pause or resume selected radio modes. Handy for attending
  nets, quieting a busy mode, to temporarily eliminate "mode monopolization", etc.
* "System Manager" added to admin page; allows you to instantly:
  * Disable / Enable the intrusive and slow Pi-Star Firewall.
  * Disable / Enable Cron, in order to prevent updates and Pi-Star services restarting during middle-of-the-night/early AM operation.
  * Enable / Disable Pi-Star Remote and Pi-Star Watchdog
* Ability to configure POCSAG hang-time from the config page.

### User Interface / Design Features

* Updated user interface elements galore, styling, wider, bigger, updated fonts, etc.
* Country-of-origin flags for callsigns.
* Improved and graphical CSS/color styling configuration page; easily change the look and feel of the dashboard.
* User-Configurable number of displayed Last Heard dashboard rows (defaults to 40, and 100 is the maximum).
* User-Configurable font size for most of the pertinent dashboard information.
* Reorganized and sectioned configuration page for better usability.
* System process status reorganized into clean grid pattern, with more core service status being displayed.
* User-Configurable 24 or 12 hour time display across the dashboard.
* Connected FCS and YSF reflector names and numerical ID both displayed in dashboard left panel.
* Additional hardware, radio and system information displayed in top header; which can be toggled.
* Admin page split up into logical sub-sections/sub-pages, in order to present
  better feedback messages when making changes.
  * Note: Last-Heard and other dynamic tables are hidden in the admin sections by default, allowing users
    to focus on the tasks-at-hand and their outputs. The Last-Heard data can be toggled in these areas, however.

### Features in Official Pi-Star Which are Intentionally Omitted in `W0CHP-PiStar-Dash`

* Upgrade notice/nag in header (unnecessary and a hacky implementation). This has been replaced by my own
  unobtrusive dashboard update notifier; displayed in the upper-right hand side of the top header.
* "GPS" link in Call Sign column of dashboard (superfluous and unreliable).
* Selectable Call Sign link to either QRZ.com or RadioID.com (both services
  suck, and the implementation of this feature is poor and unintuitive. Left
  the original function linking to QRZ.com).
* CPU Temp. in header; when CPU is running "cool" or "normal" recommended temps, the cell background
  is no longer colored green. Only when the CPU is running beyond recommended temps, is the cell colored
  orange or red.
* No reboot/shutdown nag screen/warning from admin page (Superfluous; you
  click it, it will reboot/shutdown without warning.).
* Yellow DMR Mode cell in left panel when there's a DMR network password/login
  issue (poor/inaccurate and taxing implementation, and can confuse power users that
  utilize my Instant Mode Manager, where the default cell is amber colored for
  paused modes [color is user-configurable].).

    Instead, the *actual* network name is highlighted in red when there's a login issue (courtesy of `F1RMB`'s excellent code).

## Notes about CSS, and custom CSS you may have previously applied

1. When using the `-id` option, the "normal" Pi-Star colors are used, and no CSS is installed. Any custom CSS
   you may have had, is removed but backed up. See bullet 4 below.

2. When using the `-idc` option, the `W0CHP` CSS is installed, and any of your custom CSS settings
  before installing the `W0CHP` dashboard, are backed up in the event you want to restore the official dashboard
  (see bullet 4). This is done because the CSS in the official Pi-Star is incompatible. You can still
  manually map/change your CSS back when running `W0CHP-PiStar-Dash` (see bullet 4 for details).

3. If you are already running `W0CHP-PiStar-Dash`, AND you have custom or `W0CHP-PiStar-Dash` CSS, no CSS changes, no matter which
  option you run this command with.

4. When using the `-id` option, your custom CSS settings are backed up (in the event you want to revert back
  to the official dashboard -- see  bullet 6), and the `W0CHP` dashboard uses the standard Pi-Star colors.
  This means that if you want your previous custom CSS applied to the `W0CHP` dashboard, you will need to manually
  customize your colors; You can reference the color values you had previously used, by viewing the backup file of
  your custom CSS...

        /etc/.pistar-css.ini.user

5. ...the reason for bullets 4 and 1, is because the `W0CHP` dashboard is vastly different than the official upstream version
  (completely different CSS mappings). Since this is for my personal use, I haven't added any logic to suck-in
  the user CSS values to the new mappings.

6. If you had customized CSS settings before installing the `W0CHP` dashboard, they will be restored when
  using the `-rd` option.

7. You can at any time start over and reset to the "normal" Pi-Star colors, by performing a CSS Factory Reset (`Configuration -> Expert -> Tools -> CSS Tool`).

8. If you'd like to start over with the custom `W0CHP` colors/CSS, you can copy/paste [the following values](https://repo.w0chp.net/Chipster/W0CHP-PiStar-Installer/src/branch/master/supporting-files/pistar-css-W0CHP.ini) into your `/etc/pistar-css.ini`.

## Notes about M17 Protocol Support {#m17-notes}

M17 protocol support requires updated MMDVM Modem Firmware or MMDVM HotSpot
Firmware of at least v1.6.0. Ergo, you will need to download, compile and
install the [MMDVM modem firmware](https://github.com/g4klx/MMDVM) or the
[MMDVM hotspot firmware](https://github.com/juribeparada/MMDVM_HS) yourself in
order to gain full M17 protocol support.

Please note, that if you uninstall `W0CHP-PiStar-Dash`, you will need to
downgrade the MMDVM modem or hotspot firmware back to its original firmware. For MMDVM HS
HAT users, you can simply run the following command:

```text
sudo pistar-mmdvmhshatdowngrade
```

Failure to downgrade the modem firmware when uninstalling `W0CHP-PiStar-Dash`
will result in a non-functional hot spot, since the official current Pi-Star
`MMDVMHost` binary is not compatible with newer MMDVM firmware.

## Screenshots

Not all pages shown here. Note, that you can customize the colors to your preferences...

### Main Dashboard
![alt text](https://w0chp.net/w0chp-pistar-dash/Main "Dashboard")

### Main Admin Landing Page
![alt text](https://w0chp.net/w0chp-pistar-dash/Admin.png "Admin Page")

### BrandMeister Manager
![alt text](https://w0chp.net/w0chp-pistar-dash/BM.png "BrandMeister Manager")

### Instant Mode Manager
![alt text](https://w0chp.net/w0chp-pistar-dash/IMM.png "Mode Manager")

### System Manager
![alt text](https://w0chp.net/w0chp-pistar-dash/SM.png "System Manager")

### Live Caller Screen
![alt-text](https://w0chp.net/w0chp-pistar-dash/LC.png "Live Caller Screen")

## Credits

Of course, most of the credit goes to the venerable and skilled, Andy Taylor,
`MW0MWZ`, for creating the wonderful Pi-Star software in the first place.

Credit also goes to the awesome Daniel Caujolle-Bert, `F1RMB`, for creating his
personal and customized fork of Pi-Star; as his fork was foundational and
inspirational to my `W0CHP-PiStar-Dash`.

The USA callsign lookup fallback function uses a terrific API,
[callook.info](https://callook.info/), provided by Josh Dick, `W1JDD`.

The callsign-to-country flag GeoLookup code was adopted from
[xlxd](https://github.com/LX3JL/xlxd)... authored by Jean-Luc Deltombe,
`LX3JL`; and Luc Engelmann, `LX1IQ`. [I run an XLX(d)
reflector](/xlx493-reflector/), *plus*, I was able to adopt some of its code
for `W0CHP-PiStar-Dash`, ergo, I am very grateful.
The excellent country flag images are courtesy of [Hampus Joakim
Borgos](https://github.com/hampusborgos/country-flags).

Credit must also be given to to Kim Heinz Hübel; `DG9VH`, and Hans-Juergen
Barthen; `DL5DI`, both of whom arguably created the very first MMDVM and
ircDDBGatweay dashboards (respectively); of which, spawned the entire Pi-Star
concept.

The very cool and welcome MMDVMhost log backup/restore and re-application on 
reboot code, is courtesy of Mark, `KN2TOD`.

So much credit goes toward the venerable José Uribe ("Andy"), `CA6JAU`, for his
amazing work and providing the game-changing `MMDVM_HS` hotspot firmware suite,
as well as his `MMDVM_CM` cross-mode suite.

Lastly, but certainly not least; I owe an *enormous* amount of gratitude toward
a true gentleman, scholar and incredibly talented hacker...Jonathan Naylor,
`G4KLX`; for the suite of MMDVM and related client tools. Pi-Star would have
no reason to exist, without Jonathan's incredible and prolific contributions
and gifts to the ham community.

[^1]: `W0CHP-PiStar-Dash` was not created for single-core and low-powered hardware; such as
      the first generation RPi Zero, etc. (`armv6l`). This software will run very slow on under-powered hardware.
      Please consider yourself warned. Also, please ignore all of the idiot hams on various
      support mediums saying, *"anything more than a Pi Zero is overkill"*. These ignoramuses
      have no idea what goes on under the hood in order to display meaningful info on the
      dashboard. Hint: it's a lot, and it's very resource-intensive. Ignore them...they are dumb and
      they have no idea what they are talking about.

[^2]: Piping to `bash`/shells/etc. from an online source is controversial (do
      a google search about it). However it's convenient, and one can [view & inspect
      the full & actual source code of the installer](https://repo.w0chp.net/Chipster/W0CHP-PiStar-Installer/src/branch/master/WPSD-Installer)
      prior to piping to `bash` or installing.

[^3]: `W0CHP-PiStar-Dash` occasionally queries the git repository server in
      order to determine if updates are available. In the spirit of full-disclosure,
      I wanted to mention this. This is no different than how the official Pi-Star
      software functions (but doesn't make this well-known). Additionally, every
      `W0CHP-PiStar-Dash` installation has a unique UUID generated for it; for
      web/repo-traffic capacity planning/analytics, as well as for troubleshooting
      purposes. You can find the unique UUID within the `/etc/pistar-release` file.
