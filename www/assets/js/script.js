$(function () {
	$('.images-wrapper').fadeIn(1000);

	setDownloadSubmitFormCaption();

	$(document).on('change', 'input[name="files[]"]', function () {
		setDownloadSubmitFormCaption();
	})

	function setDownloadSubmitFormCaption () {
		let submit = $('.download-form-button');

		let count = $('input[name="files[]"]:checked').length;

		if (count) {
			submit.val('Stáhnout soubory (' + count + ')');
			submit.show();
		} else {
			submit.hide();
		}
	}
});