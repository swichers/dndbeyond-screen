'use strict';

require('../css/app.css');

import CharacterUpdater from './CharacterUpdater';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

const $ = require('jquery');

$(document).ready(() => {
  $('body').tooltip({
    container: 'body',
    selector: '[data-toggle="tooltip"]',
  });
  $('body').popover({
    container: 'body',
    selector: '[data-toggle="popover"]',
    trigger: 'focus',
  });

  let character_updaters = {};
  document.querySelectorAll('.character[data-character-id]').forEach((card) => {
    let character_id = card.getAttribute('data-character-id');
    character_updaters[character_id] = new CharacterUpdater(character_id);
  });
});


