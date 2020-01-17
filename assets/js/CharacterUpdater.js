'use strict';

/**
 * A helper class to manage character updates.
 */
export default class CharacterUpdater {

  /**
   * Create a CharacterUpdater instance.
   *
   * @param {int} characterId
   *   The character Id to monitor.
   * @param {int} waitMs
   *   The time between refreshes (in milliseconds).
   */
  constructor(characterId, waitMs = 3 * 60 * 1000) {
    this.characterId = characterId;
    // The default time to wait between updates (3min).
    this.timerInterval = waitMs || 3 * 60 * 1000;

    this.startTimer();
  }

  /**
   * Updates the character information in its card.
   *
   * @returns {boolean}
   *   TRUE if an update was initiated, FALSE otherwise.
   */
  updateCard() {

    if ( ! this.getCard()) {
      return false;
    }

    this.onLoading();

    fetch(this.getUpdateUrl(), {cache: 'no-cache'})
      .then((response) => {
        return response.text().then(data => {
          if (response.ok) {
            return data;
          }

          return Promise.reject({status: response.status, data});
        });
      })
      .then((data) => {
        this.onLoaded(data);
      })
      .catch((e) => {
        this.onError(e);
      });

    return true;
  }

  /**
   * Handle the new character data being returned.
   *
   * @param {string} data
   *   The new character HTML.
   */
  onLoaded(data) {
    let card = this.getCard();
    if (card) {
      card.querySelector('.load-status').classList.add('d-none');

      let new_card_wrapper = document.createElement('div');
      new_card_wrapper.innerHTML = data;
      let new_card = new_card_wrapper.firstChild;

      let new_requested = new_card.getAttribute('data-last-requested');
      let last_requested = card.getAttribute('data-last-requested');

      if (last_requested !== new_requested) {
        card.outerHTML = data;
      }
    }
  }

  /**
   * Handle loading.
   */
  onLoading() {
    let card = this.getCard();
    if (card) {
      card.querySelector('.load-status').classList.remove('d-none');

      card.querySelector('.last-checked').setAttribute('datetime', Math.floor(new Date().getTime() / 1000));
    }
  }

  /**
   * Handle a loading error.
   *
   * @param e
   *   The error object.
   */
  onError(e) {
    let card = this.getCard();
    if (card) {
      card.querySelector('.load-status').classList.add('d-none');
      card.querySelector('.load-error').classList.remove('d-none');
    }

    this.stopTimer();

    console.error(e);
  }

  /**
   * Start the update timer.
   */
  startTimer() {
    this.timerCardId = setInterval(() => this.updateCard(), this.timerInterval);
  }

  /**
   * Stop the update timer.
   */
  stopTimer() {
    clearInterval(this.timerCardId);
  }

  /**
   * The selector to use to find the character card.
   *
   * @returns {string}
   *   The CSS selector to use.
   */
  getCharacterSelector() {
    return '.character[data-character-id="' + this.characterId + '"]';
  }

  /**
   * The Url to use when requesting updated markup.
   *
   * @returns {string}
   *   The Url to use.
   */
  getUpdateUrl() {
    return '/' + this.characterId + '/update';
  }

  /**
   * Get the DOM element that is the character card.
   *
   * @returns {Element}
   *   The character card DOM element.
   */
  getCard() {
    return document.querySelector(this.getCharacterSelector());
  }
}
