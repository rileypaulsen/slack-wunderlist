#Slackbot + Wunderlist
 The built-in Wunderlist integration in Slack is \[currently\] a pain, because any time you add a list, it has to be manually integrated through Slack's web interface.
 
 This project sets up endpoints to grab all the lists under a Wunderlist folder and add Webhook endpoints to them.
 
 Then it listens for events on the hooks and sends a Slack message to a specified channel when a task is marked complete.
 
 List deletion (a.k.a. completion) is accounted for, but the Wunderlist API doesn't seem to be reliable in firing this webhook at the time of writing.
 
 ##URIs
 
 Visit `domain.com/update` to add webhooks to any new lists in the folder

 Visit `domain.com/list` to see and confirm the existence of webhooks on lists
 
 `domain.com/complete` is the endpoint to receive webhook events
 
 ___
 
 _Use composer to install dependencies_