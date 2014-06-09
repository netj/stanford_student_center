Stanford Student Center
=======================

This repository provides a skeleton for a website that allows Stanford students to see their grades. You create a Google Doc, which is then linked to Stanford Student Center. The sharing settings have to be "Anyone with the link", and you have to enable "Publish to the web".

Grades can be entered by teaching staff in the Google Doc, and changes will be reflected on the web site.

(Note: if Google does not republish automatically each time a change is made, you also have to republish manually.)

## Installation
- Make a copy of this [fresh template sheet for CS 346 Student Center](https://docs.google.com/spreadsheet/ccc?key=0AsFqly5ZIs8LdEYxTjYxTzVsUW1pRVp3a2pjaFRKb2c). This has all the column headers that index.php will be looking for (and more).
- Set the sharing settings to be "Anyone with the link", and enable "Publish to the web". Note the document key that you later have to enter in ``constants.php''.
- Edit ``constants.php'' to reflect the class you want to use student center for.
- Upload the website (i.e. the contents of this folder) to the cgi-bin folder on Corn (note that .htaccess enables WebAuth).

## Notes
- You have to manually update index.php to parse whatever fields you update in the document (e.g. grades, questions, etc.).
