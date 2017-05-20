# Wikimedia wiki example

This small example app demonstrates a simple wiki editor and photo upload interface,
and server-side file handling.

Place the files in a webserver-accessible location and access a URL like:

* view http://127.0.0.1/wikimedia-test/index.php/api/Latest_plane_crash
* edit http://127.0.0.1/wikimedia-test/index.php/edit/Latest_plane_crash 

Note that the functionality is not complete. The TODO list includes:

* finish wiring up the bootstrap modal in edit mode, so that clicking "Save"
will POST the form attributes with the image metadata along with the file itself.
* render "article.html" with image syntax.
* serve uploaded files via symlink or PHP proxy
* allow user to indicate where in the wiki text to insert the photo,
using an API response from the server with the SHA of the uploaded photo.
* a hard problem: locking or other conflict resolution when multiple people are
uploading photos and/or making wiki edits simultaneously. Right now edits
are accepted with the assumption of some background approval or other processing
queue to resolve conflicts. The assumption is that it is better UX to accept
immediately and allow an editor to resolve potential issues later.
