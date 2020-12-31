var FRAPPER_IMGROOT = 'http://www.jorkers.com/images/templates/defaut';
var icon_person_blue, icon_person_red, icon_person_green, icon_person_white, icon_person_yellow, icon_person_orig;
var icon_place_blue, icon_place_red, icon_place_green, icon_place;
var icon_photo, icon_ballon, icon_msn_viollet;

function createGMapIcons()
{
	var baseIcon=new GIcon();
	baseIcon.shadow=FRAPPER_IMGROOT+"/marker_person_shadow.png";
	baseIcon.iconSize=new GSize(19,31);
	baseIcon.shadowSize=new GSize(34,31);
	baseIcon.iconAnchor=new GPoint(9,34);
	baseIcon.transparent=FRAPPER_IMGROOT+"/marker_person_transparent.png";
	baseIcon.imageMap=[5,1,1,5,1,11,9,29,17,11,17,5,13,1];
	baseIcon.infoWindowAnchor=new GPoint(9,2);
	baseIcon.infoShadowAnchor=new GPoint(18,25);
	icon_person_blue=new GIcon(baseIcon);
	icon_person_blue.image=FRAPPER_IMGROOT+"/marker_person_blue.png";
	icon_person_red=new GIcon(baseIcon);
	icon_person_red.image=FRAPPER_IMGROOT+"/marker_person_red.png";
	icon_person_green=new GIcon(baseIcon);
	icon_person_green.image=FRAPPER_IMGROOT+"/marker_person_green.png";
	icon_person_yellow=new GIcon(baseIcon);
	icon_person_yellow.image=FRAPPER_IMGROOT+"/marker_person_yellow.png";
	icon_person_white=new GIcon(baseIcon);
	icon_person_white.image=FRAPPER_IMGROOT+"/marker_person_white.png";
	icon_person_orig=new GIcon(baseIcon);
	icon_person_orig.image=FRAPPER_IMGROOT+"/marker_person.png";
	
	var basePlaceIcon=new GIcon();
	basePlaceIcon.shadow=FRAPPER_IMGROOT+"/marker_places_shadow.png";
	basePlaceIcon.iconSize=new GSize(21,31);
	basePlaceIcon.shadowSize=new GSize(34,31);
	basePlaceIcon.iconAnchor=new GPoint(9,34);
	baseIcon.transparent=FRAPPER_IMGROOT+"/marker_places_transparent.png";
	baseIcon.imageMap=[9,0,0,9,1,18,7,29,19,18,20,8,14,0];
	basePlaceIcon.infoWindowAnchor=new GPoint(9,2);
	basePlaceIcon.infoShadowAnchor=new GPoint(18,25);
	icon_place=new GIcon(basePlaceIcon);
	icon_place.image=FRAPPER_IMGROOT+"/marker_places.png";
	icon_place_blue=new GIcon(basePlaceIcon);
	icon_place_blue.image=FRAPPER_IMGROOT+"/marker_places_blue.png";
	icon_place_green=new GIcon(basePlaceIcon);
	icon_place_green.image=FRAPPER_IMGROOT+"/marker_places_green.png";
	icon_place_red=new GIcon(basePlaceIcon);
	icon_place_red.image=FRAPPER_IMGROOT+"/marker_places_red.png";

	var basePhotoIcon=new GIcon();
	basePhotoIcon.shadow=FRAPPER_IMGROOT+"/marker_photos_shadow.png";
	basePhotoIcon.iconSize=new GSize(23,28);
	basePhotoIcon.shadowSize=new GSize(34,28);
	basePhotoIcon.iconAnchor=new GPoint(9,34);
	basePhotoIcon.infoWindowAnchor=new GPoint(9,2);
	basePhotoIcon.infoShadowAnchor=new GPoint(18,25);
	icon_photo=new GIcon(basePhotoIcon);
	icon_photo.image=FRAPPER_IMGROOT+"/marker_photos.png";
	
	var baseIcon=new GIcon();
	baseIcon.shadow=FRAPPER_IMGROOT+"/marker_person_shadow.png";
	baseIcon.iconSize=new GSize(19,31);
	baseIcon.shadowSize=new GSize(34,31);
	baseIcon.iconAnchor=new GPoint(9,34);
	baseIcon.transparent=FRAPPER_IMGROOT+"/marker_person_transparent.png";
	baseIcon.imageMap=[5,1,1,5,1,11,9,29,17,11,17,5,13,1];
	baseIcon.infoWindowAnchor=new GPoint(9,2);
	baseIcon.infoShadowAnchor=new GPoint(18,25);
	baseIcon.infoWindowAnchor=new GPoint(13, 8);
	icon_ballon = new GIcon(baseIcon);
	icon_ballon.image=FRAPPER_IMGROOT+"/marker_jorky_brown.png";

	var baseIcon=new GIcon();
	baseIcon.shadow=FRAPPER_IMGROOT+"/ballon_shadow.png";
	baseIcon.iconSize=new GSize(22, 22);
	baseIcon.shadowSize=new GSize(30, 22);
	baseIcon.iconAnchor=new GPoint(6, 22);
	baseIcon.transparent=FRAPPER_IMGROOT+"/marker_person_transparent.png";
	baseIcon.infoWindowAnchor=new GPoint(13, 8);
	icon_msn_viollet = new GIcon(baseIcon);
	icon_msn_viollet.image=FRAPPER_IMGROOT+"/person.png";
}

createGMapIcons();
