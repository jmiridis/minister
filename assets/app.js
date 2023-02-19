/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/sass/app.scss'

// start the Stimulus application
import './bootstrap'

import $ from 'jquery'

$('a.faq-btn')
  .bind('click', function(evt) {
    evt.preventDefault()

    $(this).closest('.faq-row').find('.answer').toggle()
  })
  .bind('mousedown', function(evt) {
    evt.preventDefault()
  })


$('div.form-switch input.form-check-input.dynamic').bind('change', function(evt) {
  evt.preventDefault()

  let el = $(this)
  el.prop('disabled', true)
  let url = el.data('url')
  let isChecked = el.is(':checked')
  console.log('is checked: ' + isChecked)
  $.post(url, {checked: isChecked}, function(data) {
    console.log(data)
    if (data.success) {
      el.prop('checked', data.checked)
    }
    el.removeAttr('disabled')
  })
})