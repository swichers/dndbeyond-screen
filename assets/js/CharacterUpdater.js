'use strict';

export default class CharacterUpdater {

  constructor(characterId) {
    this.characterId = characterId;
    this.timerInterval = 5 * 60 * 1000;

    this.startTimer();
  }

  update() {
    let that = this;
    let url = '/' + this.characterId + '/update';

    let card = document.querySelector(this.getCharacterSelector());
    if ( ! card) {
      return false;
    }

    card.querySelector('.load-status').classList.remove('d-none');

    fetch(url, {cache: 'no-cache'})
      .then((response) => {
        return response.text().then(data => {
          if (response.ok) {
            return data;
          }

          return Promise.reject({status: response.status, data});
        });
      })
      .then((data) => {
        card.outerHTML = data;
      })
      .catch((e) => {
        card.querySelector('.load-status').classList.add('d-none');
        card.querySelector('.load-error').classList.remove('d-none');
        that.stopTimer();
      });
  }

  startTimer() {
    this.timerId = setInterval(() => this.update(), this.timerInterval);
  }

  stopTimer() {
    clearInterval(this.timerId);
  }

  getCharacterSelector() {
    return '.character[data-character-id="' + this.characterId + '"]';
  }
}
