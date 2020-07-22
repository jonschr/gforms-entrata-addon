# Entrata for Gravity Forms

This plugin adds feeds capabilities for Entrata, using the [sendLeads API for Entrata](https://www.entrata.com/api/v1/documentation/sendLeads). The methodology used is standard, allowing for you to link up the basic custom fields in Entrata with your form fields.

The following fields are supported in this implementation:
* First name
* Last name
* Email
* Phone
* Message

You will need to supply the following information on the backend of the site:
* Entrata endpoint URL (the place where we send requests)
* Username
* Password
* Property ID
* Lead source ID

## Issues

If you're having trouble using this, try using a free service like [PipeDream](https://pipedream.com/) to test your requests to get better visibility into what you're seeing. Failing that, you can enable logging for the Entrata Gforms addon, then check what responses you're getting from the Entrata API.
 
