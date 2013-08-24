voip.ms Click2Call System
=========================

Using this script, you can initiate a callback to any phone number. This will give you a dial tone to dial out using your voip.ms account.  
Great to call long distance from your cell phone, or when you're at a relative's place, and want to call long distance without incurring any fees to them (it probably costs them nothing to receive calls, right?)

How It Works
------------
- If you use Save For Later, your information is saved in a JSON file on the server, named with the GUID that is uniquely generated when saving. When you later call the page and provide that GUID, the script will load that JSON file, and execute a Click2Call.
- During a Click2Call, the voip.ms API is used to: 1) confirm that the specified DID exists; 2) confirm that the specified Number to call is configured as a Callback: this makes the system more secure, as it can only be used to callback numbers you have already configured in your account; 3) Create a CallerID Filter, that will be used to trigger the Callback.
- After the above checks are made using the voip.ms API, the CallerID Filter will be modified (if needed) to route to the proper Callback. Then, Google Voice is used to call your DID: this will trigger the CallerID filter (which is configured for CallerID = the Google Voice number). The CallerID Filter is routed to the Callback for the specified number, so that phone will ring. Answering that call will give you a voip.ms dial tone. Dial any number, and your voip.ms account will be charged for the call.

Requirements
------------
- A web host that can run PHP, and has the cURL extension enabled.
- If you want to use the Google Voice-activate callback, you will also need the [google-voice-click2call](https://github.com/gboudreau/google-voice-click2call) script, and to be able to use the PHP passthru() function to execute it. Not many shared hosts will allow such a thing.

Installation
------------
- Configure the very few settings at the top of the `index.php` file.
- Upload the files to your web host.
- Make sure the `config/` folder is writable by the apache process, or remove the __Save for Later__ button from the UI.

Using it
--------
Simply open the URL to index.php in your browser. Follow the instructions on how to setup your voip.ms account.
