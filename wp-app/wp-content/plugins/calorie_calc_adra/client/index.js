const { render, html, Component } = window.htmPreact;

class AddButton extends Component {
  render({ exercise, kcal, onClick, ...props }) {
    return html`
      <button ...${props} onClick=${onClick}>${exercise} (${kcal} kcal/min)</button>
    `;
  }
}


class PlanItem extends Component {
  render({ exercise, kcal, onClick, ...props }) {
    return html`
      <li>
        <input />
        <button>usuń</button>
      </li>
      `;
  }
}

// TODO: pobieranie przez a.download

class CalorieCalculator extends Component {
  addToPlan(exercise) {
    const { plan = [] } = this.state;
    this.setState({ plan: plan.concat(exercise) });
  }

  render({ exercises = [] }, { plan = [] }) {
    return html`
      <div>
      <h3>Dodaj ćwiczenie</h3>
      <div>
        ${exercises.map(exercise => html`
          <${AddButton} ...${exercise} onClick=${e => this.addToPlan(exercise)} />
        `)}
      </div>
      <div>
        <ul>
          ${plan.map(item => html`<${PlanItem} ...${item} />`)}
        </ul>
      </div>
      <div>
      ${plan.length > 0 ? (
        html`
          <button>Eskportuj do CSV</button>
        `
      ): null}
      </div>
      </div>
    `;
  }
}

const API_URL = '/?rest_route=/calorie-calc/v1';

const api = {
  getExercises: () => fetch(`${API_URL}/list`).then(resp => resp.json())
};

api.getExercises()
  .then((exercises) => {
    render(html`<${CalorieCalculator} exercises=${exercises}/>`, document.querySelector('#ccalc_root'));
  })
  .catch(() => {
    render(html`<p>Błąd połącznia z serwerem!</p>`);
  });
