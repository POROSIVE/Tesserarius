# Devlogs
start of the devlogs, all the things done within the first two day of working on this was debugging and fixing the component routing issue and making simple landing page taken from other projects of mine.

## 10/11/26
### Implemented alerter
Finally fully added alerter implementation converted from the cgcc project including adding the styling, I'll try to make improvement but I won't change things like this if it's already worked perfectly.
<br>
I think I forgot to make notes about the navbar components but it's pretty much just a standalone navbar made to be reusable

## 12/11/26
## added auth verification function, fixed typo
the `auth_required` function will serve as single call check of whether or not user is authenticated, Found the __DIR__ typo while working on testing out all function on config.php

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

## 15/11/26
Been spending the whole day trying to reimplement session properly, haven't tested with the real page yet.
### SessionManager class, universal date helpers, and quote 
Everything up to `get_prev_loc` in config.php was mostly just switched from single quotes (') to double quotes (") for code consistency, while the actual big change is between `get_prev_loc` and the old temporary `auth_required` function.

### Added universal date and unix time functions
Finally figured out the way to make the date comparison worked, well kind of as I found out it still need to use the "Y-m-d" format like I've been using before but unlike that time I do know this is actually the correct way and that unix conversion can convert the "Y-m-d" to "d-m-Y" format
- `get_current_time()` generates the Y-m-d format for database comparisons. <br>
- `get_unix()` converts raw date strings (supporting both Y-m-d and d-m-Y formats) into Unix timestamps so expiration checks are compared as actual numbers instead of broken strings. <br>
- `reformat_date()` converts the database date back into d-m-Y H:i:s for displaying on the page so the time format stays universal and readable on the frontend. 

### SessionManager class
Replaced the barren session handling with a full SessionManager class adapted/inspired from the cgcc so session tokens, cookies, and database logs (session_logs and org_session_logs) are all handled in one place:
- Cookie handling (setCookie & clearCookie): Manages session_token cookies with httponly, samesite = Lax, and $base_path awareness, and cleans up both $_SESSION and $_COOKIE states on logout or invalidation. <br>
- Session limit & cleanup (purgeExpired & getSessionCount): Purges expired rows from session_logs and org_session_logs and limits each account to a maximum of 3 active sessions (MAX_SESSIONS = 3) before blocking new logins. <br>
- Session creation & destruction (createSession & webSessionDestroy): Generates a 64-character hex token (random_bytes(32)), logs the session along with osid, client_type, logged_address, and 15-day expiration (SESSION_TTL) into session_logs, and deletes active user/org session rows from the database when logging out. <br>
- Verification & auto-extend (webSessionVerify, extendsSession, & SessionManager::auth_required): Re-verifies the logged-in account (approval_state = 'approved') and active session token against the database every 60 seconds (REVERIFY_INTERVALS), restores sessions from $_COOKIE["session_token"] with session_regenerate_id(true) if the PHP session expired first, and automatically slides the expiration date forward when less than 7 days remain (SESSIONS_EXTEND_THRESHOLD). <br>
I do Kept the old standalone auth_required() function at the very bottom temporarily just incase that the things just want to break in the most confusing way like the other time.

## 18/11/26
### Registration page, DB transaction helpers, and minor tweaks
Created the registration page (apps/auth/register/app.php) and added the needed DB helpers in config.php so account and profile creation don't leave half-inserted rows if one of the queries failed.

### Updated DB class in config.php
Added a couple more static methods to the DB class to handle multi-table inserts properly:
- `DB::lastId()` and `DB::affectedRows()` for grabbing the `insert_id` and `affected_rows` from the active connection. 
- `DB::beginTransaction(),` `DB::commit(),` and `DB::rollback()` for wrapping related queries into a single database transaction so it rolls back cleanly if an error happens midway. 

### Created registration page and placeholder activate page
Made the `/auth/register` page using the same layout styling as the login page along with a client-side checkEmail() check hooked to alerter():
- Validates email, username (regex check and length limit), and making password require minimum 8 characters, then checks the account table to make sure the username or email isn't already taken. 
- Generates the password hash (password_hash), a unique profile_tag ($username . bin2hex(random_bytes(8))), join date (d-m-Y), and default JSON data for software collection, badges, and user preferences (private & themes). 
- Added AUTO_APPROVE_USERS env check (defaults to true for local dev) — if enabled, sets approval_state directly to approved; otherwise sets it to pending and generates a SHA-256 activation_token_hash with a 1-hour expiration (activation_expires). 
- Wraps both the account and profile table inserts inside a transaction block and redirects to the login page on success. 

Also added an empty apps/auth/activate/app.php file (declare(strict_types=1);) as a placeholder for the account activation route later, adjusted ReverseTimer animation in script/alert.js from 4.5s to 4.4s so the border timing matches better, recompiled global.css, and updated README.md with the `AUTO_APPROVE_USERS=true` on the .env note and minor typo fixes.

### fixing another stupid typo/self-inflicted-bugs
I can't believed that I forgot to use the encoded array and check if the registration actually worked, it does now tho some feature like emailing is still left to be figured out