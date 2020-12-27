# Hide my profile for phpBB3

phpBB extension that allows users to be able to hide their profile.  Permissions based and admins and mods always have the ability to view a users profile.

[![Build Status](https://github.com/rmcgirr83/hidemyprofile/workflows/Tests/badge.svg)](https://github.com/rmcgirr83/hidemyprofile/actions)

## Installation

### 1. clone
Clone (or download and move) the repository into the folder ext/rmcgirr83/hidemyprofile:

```
cd phpBB3
git clone https://github.com/rmcgirr83/hidemyprofile.git ext/rmcgirr83/hidemyprofile/
```

### 2. activate
1. Go to admin panel -> tab customise -> Manage extensions -> enable Hide My Profile
2. Set the permissions for the groups/users you want to be able to hide their profiles
3. Once set a new entry for them to choose will display in the user control panel under Board preferences

## Update instructions:
1. Go to your phpBB Board > Admin Control Panel > Customise > Manage extensions > Hide My Profile: disable
2. Delete all files of the extension from ext/rmcgirr83/hidemyprofile
3. Upload all the new files to the same locations
4. Go to your phpBB Board > Admin Control Panel > Customise > Manage extensions > Hide My Profile: enable
5. Purge the board cache
