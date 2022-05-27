This is GabeG888's entry to SecurIT360's Offensive Coding Challenge 2022.



The contest was to create an application with the following specifications:

4. Business Requirements:
- Application must provide authentication
- Authentication must defeat velocity attacks (account brute forcing, cred stuffing, etc.)
- Application must provide secure session management
- HTTPS/TLS is not required
- The following features must exist inside the application:
- Secure File upload, only allowing PDF,TXT files
- Free form text field associated with the file upload feature (think like an image tag, or image description)
- Application should look professional if possible - this will not impact your scoring

5. Functional Requirements:
- Application must be packaged into docker image
- Contest Judges must be able to run the docker package locally – include instructions
- All source code must be provided
- Bonus points – Application should provide security logging to a local logfile
     - Events such as failed logins, failed file uploads, etc.
- No preference on coding language or libraries
- Application submission will only be accepted in the form of a GITHUB repo. (e.g. https://github.com/student1/codingproject001)



Instructions:
1. Clone this repo
2. Enter the directory
3. Use "chmod +x" to make all .sh files executable
4. Run "./start.sh" to start the docker container
5. Go to http://localhost:888
6. Use "./restart.sh" to restart or "./stop.sh" to stop the docker container



Info:
Security events are logged to /var/www/html/security.log in the docker
Some useful debugging php pages are in the debug directory, copy them to the app directory to enable them
