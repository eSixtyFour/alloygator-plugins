# Alloygator WordPress Plugins
 
This repo contains 4 separate WordPress plug-ins developed for a client and is not available for public use. Is has been published as an example of some of my code.

The 4 plug-ins are;

## Find A Fitter

This plug-in uses PHP, Javascript with Ajax, Google Maps and Google Geolocation APIs and CSS.

URL to live version: https://alloygator.com/uk/find-a-fitter/

This plug-in works with Google Maps to allow a user to locate the nearest AlloyGator fitter. From the WordPress MySql database it pre-populates a Javascript array which is used to place customer markers for each fitter on the UK map.

There are two types of fitter, standard and 5-star and these are identified with different marker pins. Users can search for a fitter based on their current location, or by entering a postcode and selecting a radius to indicate how far they are willing to travel.

Once they search, the google map is displayed showing the fitters within the radius. Using drag and drop the radius can be moved or altered and the search results automatically update to show the fitters that fall within the new radius.

There are settings in the plug-ins admin pages that alow the website admin to set initial zoom levels of the map.

The plug-in also integrates with wooCommerce to allow users to add fitters to their basket and the order delivery address becomes the selected fitters address.

## Colour Selector

This plug-in uses Javascript, JQuery, the Canvas object and CSS.

URL to live version: https://alloygator.com/uk/colour-selector/

The colour selector is a tool to help users visualise what their car could look like with some AlooyGators fitted. Using the dropdown options the user can chose their car colour, alloy wheel colours and selected AlloyGator colour to see what it looks like on a photo-realistic car.

If they like what they see, they can create an image of their choice for saving for sharing.

## fooLogoDisplay

Uses basic Javascript and CSS

URL to live version: https://alloygator.com/uk/gallery/

This plug-in modifies an existing plug-in to display car log graphics on the buttons to filter the images inthe gallery

## csvExportModifier

Uses PHP

This admin plug-in modifies and existing CSV export module to alter the rows and columns created in the export.

