# FormGrab

## Introduction

FormGrab provides a simple way to add forms to your ExpressionEngine site. It lets you easily create new forms, and then save and/or email form submissions.

It provides a thin-wrapper around your own form designs so you can control the way your form looks and works, and gives you a friendly control panel for managing the form and its submissions.

## Installation

Copy the `formgrab` folder into your `system/user/addons` folder and install it from the ExpressionEngine Control Panel Add-ons page.

## Usage

### Creating a form

Forms are created by submitting data to a particular url. This can be manualy created using the url found here ...

... but the best way to do it is to use the `{exp:formgrab:form}` tag. Here's an example of a simple contact form:

	{exp:formgrab:form
		name="contact_form"
		title="Contact Form"
	}

		<div class="form-group">
			<label for="name">Name</label>
			<input name="name" type="text" class="form-control" id="name" placeholder="Your name">
		</div>
		<div class="form-group">
			<label for="email">Email address</label>
			<input name="email" type="email" class="form-control" id="email" placeholder="Your email address">
		</div>
		<div class="form-group">
			<label for="message">Message</label>
			<textarea name="message" class="form-control" id="message" rows="3"></textarea>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>

	{/exp:formgrab:form}


The only required parameter is the `name=` parameter which is used to identify this (and subsequent) form submissions. This should be unique for each type of form on the site.

The code between the `{exp:formgrab:form}` tags can be anything you desire so there are no restrictions to how you want to style the form.

Available parameters are:

#### name

	name=

This is the only required parameter and is used to identify the form.

#### title

	title=

Optional. This gives the form a more 'friendly' name in the Control Panel. It is only used when the form is created/ The title can be changed from the form's settings in the Control Panel.

#### form_class

	form_class=

Optional. This applies a class attribute to the generated `<form>` tag.

#### form_id

	form_id=

Optional. This applies an id attribute to the generated `<form>` tag.

When the form is submitted for the first time, it appears in the control panel and can be configured to work the way you want.

#### required

	required=

Optional. This provides basic 'required field' validation. You supply list of fields (separated by a | character) that must not be empty for the form to validate (eg, `required="name|email|message"`).

#### attributes

	attributes=

Optional. This allows you to add additional attributes to the form tag (eg, `attributes="novalidate"`, or `attributes='novalidate data-attribute="value"'`).

### Form options

When the form has been submitted for the first time it will appear in the Control Panel.

From here you can configure its behaviour. Options include whether the data should be saved to the database and/or whether the data is emailed to designated email address.

You can also specify a URL for the user to be redirected to on submission.

Subsequent submissions will start appearing in the submission list with option to view the full details, and to mark the submissions as new, read, or archived. They can also be deleted and exported to CSV.

### Limitations

FormGrab does not currently support file upload fields, and only has rudimentary support for multiple selection fields (eg, select tags with the multiple attribute or multiple checkboxes).

## Support

The recommended way to get help is to email <a href="mailto:support@brandnewbox.co.uk">support@brandnewbox.co.uk</a>.
