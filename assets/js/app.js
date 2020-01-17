'use strict';

require('../css/app.css');

import CharacterUpdater from './CharacterUpdater';
import TimeSince from './TimeSince';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// @TODO: Replace jquery with jquery-slim.
const $ = require('jquery');

$(document).ready(() => {
  // Init the tooltips we have for various properties.
  $('body').tooltip({
    container: 'body',
    selector: '[data-toggle="tooltip"]',
  });
  // Init the popovers we use to show spell information.
  $('body').popover({
    container: 'body',
    selector: '[data-toggle="popover"]',
    trigger: 'focus',
  });

  let character_updaters = {};
  // Start the automatic refreshing for all available characters.
  document.querySelectorAll('.character[data-character-id]').forEach((card) => {
    let character_id = parseInt(card.getAttribute('data-character-id'));
    character_updaters[character_id] = new CharacterUpdater(character_id);
  });

  (new TimeSince());
});
