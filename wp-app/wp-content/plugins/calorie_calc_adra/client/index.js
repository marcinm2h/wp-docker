const { render, html, Component } = window.htmPreact;

const download = (filename, text) => {
  const element = document.createElement('a');
  element.setAttribute('href', `data:text/plain;charset=utf-8,${encodeURIComponent(text)}`);
  element.setAttribute('download', filename);
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
};

class AddButton extends Component {
  render({ exercise, kcal, onClick, ...props }) {
    return html`
      <button ...${props} onClick=${onClick}>${exercise} (${kcal} kcal/min)</button>
    `;
  }
}

class PlanItem extends Component {
  render({ exercise, kcal, minutes, onRemove, onMinutesChange, ...props }) {
    return html`
      <li>
        <label>
          ${exercise} <input
            type="number"
            min="0"
            onInput=${e => onMinutesChange(Number(e.target.value))}
          /> min
        </label>
        ${ minutes ? minutes * kcal : undefined} kcal
        <button onClick=${e => onRemove()}>usuń</button>
      </li>
      `;
  }
}

class CalorieCalculator extends Component {
  state = {
    uuid: 0,
    plan: []
  }

  addItemToPlan(exercise) {
    this.setState({ plan: this.state.plan.concat({ ...exercise, uuid: this.state.uuid++ }) });
  }

  updateItem(uuid, newState) {
    this.setState({
      plan: this.state.plan.map(item => {
        if (item.uuid !== uuid) {
          return item;
        }
        return {
          ...item,
          ...newState
        }
      })
    });
  }

  removeItem(uuid) {
    this.setState({
      plan: this.state.plan.filter(item => item.uuid !== uuid)
    })
  }

  generateSummary() {
    return this.state.plan.reduce((acc, item) => ({
      minutes: acc.minutes + item.minutes,
      kcal: acc.kcal + item.kcal * item.minutes
    }), { minutes: 0, kcal: 0 })
  }

  exportToCsv() {
    const data = {
      exercises: this.state.plan.map(item => ({
        name: item.exercise,
        minutes: item.minutes,
        'kcal/min': item.kcal,
        kcal: item.kcal * item.minutes
      }))
    };

    api.exportToCsv(data).then(csv => {
      download('export.csv', csv);
    });
  }

  render({ exercises = [] }, { plan = [] }) {
    const summary = this.generateSummary();

    return html`
      <div>
      <h3>Dodaj ćwiczenie</h3>
      <div>
        ${exercises.map(exercise => html`
          <${AddButton}
            ...${exercise}
            onClick=${e => this.addItemToPlan({ ...exercise, minutes: 0 })}
          />
        `)}
      </div>
      <div>
        <ul>
          ${plan.map(item => html`
            <${PlanItem}
              ...${item}
              onMinutesChange=${minutes => this.updateItem(item.uuid, { minutes })}
              onRemove=${() => this.removeItem(item.uuid)}
            />
          `)}
        </ul>
      </div>
      <div>
      ${plan.length > 0 ? (
        html`
          <hr />
          <h3>Podsumowanie</h3>
          <label>
            Minut łączne: <input disabled value=${summary.minutes} />
          </label>
          <label>
            Kalorii łączne: <input disabled value=${summary.kcal} />
          </label>
          <button onClick=${e => this.exportToCsv()}>Eskportuj do CSV</button>
        `
      ) : null}
      </div>
      </div>
    `;
  }
}

const API_URL = '/?rest_route=/calorie-calc/v1';

const api = {
  getExercises: () => fetch(`${API_URL}/list`).then(resp => resp.json()),
  exportToCsv: (data) =>
    fetch(`${API_URL}/export`, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json'
      }
    }).then(resp => resp.text())
};

api.getExercises()
  .then((exercises) => {
    render(html`<${CalorieCalculator} exercises=${exercises}/>`, document.querySelector('#ccalc_root'));
  })
  .catch(() => {
    render(html`<p>Błąd połącznia z serwerem!</p>`);
  });
