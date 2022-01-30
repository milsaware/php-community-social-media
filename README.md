# PHP Community Social Media

**A work in progress**

A server with access to one directory above the public folder is required to install and use this script. Upload the contents of httpd.www to your public folder, upload httpd.private to one directory above your public directory. Your public directory may be named something other than **httpd.www**. It may, for instance, be called **public** or your username. If this is the case you must open httpd.private/system/bootstrap.php and change all instances of **httpd.www** to the name of your public directory. Do the same for **httpd.private** to whatever your private directory is called.

The app runs on SQLite so there's no need for any database installation.

There currently isn't a CMS associated with the app so all modifications need to be done manually.

**Features**

- Responsive design
- Sign up and log in functionality
- Auto update of notification
- Like/Dislike posts
- Responses in timeline show in the same block
- Ability to follow and unfollow accounts
- Respond to comments
- Post new comments
- Update banner and icon
- Profile shows previous posts, responses, reposts, followers and following
- Instant post to timeline
- Mute function
- Bio creation

**Known issues**

- CMS needs to be built
- Respond icon doesn't show green in profile responses
- **forgot password** function doesn't exist
- API needs to be written to allow communication between nodes
- Styling for different monitors needs to be finished

**Languages used**

PHP, HTML, Javascript, CSS

**Framework**

ozboware PHP MVCF 1.4.4

**screenshots**
![homescreen](https://user-images.githubusercontent.com/95859352/151692269-bcdd0d1d-e7ba-414b-a884-b67684737423.png)
![timeline](https://user-images.githubusercontent.com/95859352/151692274-3217f34f-d6cd-40c1-a1b2-2b3222af3ec2.png)
![timeline 2](https://user-images.githubusercontent.com/95859352/151692279-e70169e4-af3a-4bb4-874c-8a76ec091ec9.png)
![search posts](https://user-images.githubusercontent.com/95859352/151692283-193c0894-cca8-41e4-b970-6fe0b0396e7e.png)
![profile posts](https://user-images.githubusercontent.com/95859352/151692296-21910fee-e0b3-497e-8f6c-9a45398b7651.png)
![profile responses](https://user-images.githubusercontent.com/95859352/151692302-83ea1e23-4c99-4dbd-b830-018687ee7f24.png)
![profile reposts](https://user-images.githubusercontent.com/95859352/151692309-5627fc07-242c-43ef-9728-9988e8f390b2.png)
![profile following](https://user-images.githubusercontent.com/95859352/151692311-dca11c8f-4e0a-47a8-b775-e12c7e40df33.png)
![profile followers](https://user-images.githubusercontent.com/95859352/151692314-7738a6e4-dbc3-4089-a3a3-b61fe46670a4.png)
![notifications new](https://user-images.githubusercontent.com/95859352/151692318-41596030-3f00-4aad-8e80-0f90426f8f0d.png)
![notifications old](https://user-images.githubusercontent.com/95859352/151692320-6cd6177e-829e-4566-b9d9-d170857056e3.png)
![settings](https://user-images.githubusercontent.com/95859352/151692324-e09826fc-e46e-47fc-b147-3f18f731b27d.png)

![mobile post](https://user-images.githubusercontent.com/95859352/151692337-4ee0301d-dabd-4abd-9d6d-86ac098694cf.png)
![mobile settings](https://user-images.githubusercontent.com/95859352/151692344-4360241b-e320-4653-ba54-2f42b8aff3fa.png)
![mobile search people](https://user-images.githubusercontent.com/95859352/151692349-916a46c1-e693-40a6-8c1c-44a93a7fcb06.png)
![mobile timeline](https://user-images.githubusercontent.com/95859352/151692354-5c2f3ed3-6cca-45c3-9316-e1e4948e758d.png)
![mobile search](https://user-images.githubusercontent.com/95859352/151692356-7fdee29a-d3df-4944-84b0-b734f480abac.png)
