# grandstrand-pdf
PDF plugin for TSML customized for the Grand Strand Intergroup

This is a quick and dirty edit to the original [nyintergroup-pdf](https://github.com/code4recovery/nyintergroup-pdf) plugin from the great devs at [code4recovery](code4recovery.github.io).

Feel free to fork, customize, and reuse if desired. Shortcode is \[pdf-form\]

Current version can be seen and tested at [aamyrtlebeach.org/pdf/](https://aamyrtlebeach.org/pdf/)

Main Changes:
* PDF Generation can be done by non-logged in users and interface for doing so is enhanced with some quick and dirty js
* Created cover pages with local specifc information and legend.  These can be found in pdf.php $coverHTML and $innerHTML.  [TCPDF](https://tcpdf.org/examples/) is really weird with what CSS it will accept.
* Updated to latest version of [TCPDF](https://github.com/tecnickcom/tcpdf) which they consider obsolete at this time however new version is not marked as stable yet.
* Changed the way legend for meeting types are displayed and not using the original "symbols" which were confusing.
* Include group notes in left column for 1 off meetings like Last Saturday Speaker Meeting.  Location notes are still there as well.

To Do:
* Actually learn PHP (most PHP implemented here was on the fly learning so it is likely very poor practice)
* Learn Laravel and work on current [code4recovery PDF](https://github.com/code4recovery/pdf)
* Cleanup commented code and remove some things that are no longer being used like symbols
* Get code to loop through our districts rather than having to call seperate functions for each one.  I tried and wasn't successful.  I also tried setting our regions as a heirarchial with Districts being a top level without success as it looks like the code already tries to do this but I just got a blank PDF.
