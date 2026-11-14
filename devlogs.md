# Devlogs
start of the devlogs, all the things done within the first two day of working on this was debugging and fixing the component routing issue and making simple landing page taken from other projects of mine.

## 10/11/26
### Implemented alerter
Finally fully added alerter implementation converted from the cgcc project including adding the styling, I'll try to make improvement but I won't change things like this if it's already worked perfectly.
<br>
I think I forgot to make notes about the navbar components but it's pretty much just a standalone navbar made to be reusable

## 12/11/26
## added auth verification function, fixed typo
the "auth_required" function will serve as single call check of whether or not user is authenticated, Found the __DIR__ typo while working on testing out all function on  config.php

## more flexible body class
because on some page will be needing different styling and container types

## created the login page, made mock profile page, and some other things 
Everything made at the time of writing will probably change alot in the near future. It's better to test whether or not the password_verify is deciding to broke again like the other time it happened in the other projects, thankfully it does work though this auth system is still barren but it should be okay if the current version is already working.
the profile page created just for testing the auth required function and nothing else, the database was still in planning at the time of writing and the project is still using empty database with one account table for this test.
Added the nav button to profile page because it has become a hurdle going back and forth the page.
I've taken some styling from the other project to here because I like it and don't want to restyle it again.

## 13/11/26
### Added callable env function, removed the category button and corrected the save_prev_loc placement
For consistency and that I don't want to declare the $env each time it used on different page.
Also removed the category button on the home page for the moment being unused, and correcting the save_prev_loc placement that previously makes no effect when user try to visit