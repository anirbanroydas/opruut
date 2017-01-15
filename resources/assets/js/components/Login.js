import React from 'react';
import { Field } from 'redux-form';
import classnames from 'classnames';

import { signInUserReduxForm } from '../actions/AuthActions';

import loginStyles from '../../sass/components/login.scss';




class Login extends React.Component {
	
	constructor(props) {
	  super(props);
	
	  this.renderField = this.renderField.bind(this);
	  this.handleFormSubmit = this.handleFormSubmit.bind(this);
	}



	handleFormSubmit(values, dispatch) {
	
		// this.props.actions.signInUser(values);
		return signInUserReduxForm(values, dispatch);
	
	}


	
	renderField({ input, label, type, meta: { touched, error } }) {
		
		return (

			<fieldset className={ classnames('form-group', loginStyles.formGroup,  { 'has-error': touched && error, [loginStyles.hasError]: touched && error} ) } >
				<label className={ classnames('control-label', loginStyles.controlLabel) } >{label}</label>
				<div>
					<input {...input} placeholder={label} className={ classnames('form-control', loginStyles.formControl) }  type={type} />
					{touched && error && <div className={ classnames('help-block', loginStyles.helpBlock) } >{error}</div>}
				</div>
			</fieldset>
		);
	
	}



	submitButtonText() {
		return this.props.submitting ? 'Logging in...'  : 'Log in';
	}




	// renderAuthenticationError() {
		
	// 	if (this.props.error) {
		
	// 		return (

	// 			<div className={ classnames('alert', 'alert-danger', loginStyles.alert, loginStyles.alertDanger) } >
	// 				{ this.props.error }
	// 			</div>
	// 		);
		
	// 	}

	// 	return <div></div>;
	// }





	render() {
		
		return (

			<div className={ classnames('col-md-6', 'col-md-offset-3', loginStyles.loginContainer) } >
				<h2 className={ classnames(loginStyles.formSigninHeading) } >Log in now.</h2>

				<form onSubmit={this.props.handleSubmit(this.handleFormSubmit)}>
					<Field name="email" component={this.renderField} className={ classnames('form-control', loginStyles.formControl) }  type="text" label="Email"/>
					<Field name="password" component={this.renderField} className={ classnames('form-control', loginStyles.formControl) }  type="password" label="Password"/>
					<button action="submit" className={ classnames('btn', 'btn-primary', 'btn-lg') } disabled={this.props.submitting || this.props.pristine} > 
						{ this.submitButtonText() } 
					</button>
				</form>

				{/* { this.renderAuthenticationError() } */}
				
			</div>
		);
	
	}

}






Login.propTypes = {
	
	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.oneOfType([
	    React.PropTypes.oneOf([null]),
	    React.PropTypes.object
	]),
	actions: React.PropTypes.object,
	handleSubmit: React.PropTypes.func
};






export default Login;

