import React from 'react';
import { Field } from 'redux-form';
import classnames from 'classnames';

import { signUpUserReduxForm } from '../actions/AuthActions';

import signupStyles from '../../sass/components/signup.scss';


class Signup extends React.Component {
	
	constructor(props) {
	  super(props);
	
	  this.renderField = this.renderField.bind(this);
	  this.renderRadioField = this.renderRadioField.bind(this);
	  this.handleFormSubmit = this.handleFormSubmit.bind(this);

	}


	handleFormSubmit(values, dispatch) {
	
		// this.props.actions.signUpUser(values);
		return signUpUserReduxForm(values, dispatch);
	}

	
	renderField({ input, label, type, meta: { touched, error, asyncValidating } }) {
	
		return (
			<fieldset className={ classnames('form-group', signupStyles.formGroup, {'has-error': touched && error, [signupStyles.hasError]: touched && error} ) } >
				<label className={ classnames('control-label', signupStyles.controlLabel,  signupStyles.controlLabel ) } > {label} </label>
				<div className={ classnames({'async-validating': !!asyncValidating } ) } >
					<input {...input} placeholder={label} className={ classnames('form-control', signupStyles.formControl) } type={type} />
					{touched && error && <div className={ classnames('help-block', signupStyles.helpBlock) } >{error}</div>}
					{!!asyncValidating && <div className={ classnames('help-block', signupStyles.helpBlock) } >Checking....</div>}
				</div>
			</fieldset>
		);
	}


	renderRadioField({ input, label, type, options, meta: { touched, error } }) {
		// console.log('renderRadioField : input : ', input, ' label : ', label, ' type : ', type, ' touched : ', touched, ' error : ', error);
		return (
			
			<fieldset  className={ classnames('form-group', signupStyles.formGroup, {'has-error': touched && error, [signupStyles.hasError]: touched && error} ) }>	
				<label className={ classnames('control-label', signupStyles.controlLabel,  signupStyles.controlLabel ) } >Gender</label>
				<div>
					
					{ options.map(o => <label key={o.value} className={ classnames('radio-inline') }>
								<input type="radio" {...input} value={o.value} checked={o.value === input.value} /> {o.title}
					  		</label>
					  	)
					}
				</div>
				{touched && error && <div className={ classnames('help-block', signupStyles.helpBlock) } >{error}</div>}
			</fieldset> 		
			
		);
	}


	submitButtonText() {
		return this.props.submitting ? 'Signing up...'  : 'Sign up';
	}



	// renderAuthenticationError() {
		
	// 	if (this.props.auth.error) {
	// 		// console.log('Auth Erro : ', this.props.auth.error);

	// 		return (
	// 			<div className={ classnames('alert', 'alert-danger', signupStyles.alert, signupStyles.alertDanger) } >
	// 				{ this.props.auth.error }
	// 			</div>
	// 		);
		
	// 	}

	// 	return <div></div>;
	// }





	render() {
		// console.log('Signup form : this.props : ', this.props);
		return (

			<div className={ classnames('col-md-6', 'col-md-offset-3', signupStyles.signupContainer) } >
				<h2 className={ classnames(signupStyles.formSignupHeading) } >Find the routes now.</h2>

				<form onSubmit={this.props.handleSubmit(this.handleFormSubmit)}>
					<Field name="name" type="text" component={this.renderField} label="Name"  />
					<Field name="email" type="text" component={this.renderField} label="Email"  />
					<Field name="password" type="password" component={this.renderField} label="Password"  />
					<Field name="password_confirmation" type="password" component={this.renderField} label="Password Confirmation"  />
					<Field name="gender" component={this.renderRadioField} required={true} options={[
					    { title: 'Male', value: 'male' },
					    { title: 'Female', value: 'female' },
					    { title: 'Other', value: 'other' }  
					]} />
					<button action="submit" className={ classnames('clearfix', 'btn', 'btn-primary', 'btn-lg') } disabled={this.props.submitting || this.props.pristine || this.props.invalid} > 
						{ this.submitButtonText() } 
					</button>
				</form>

				{/* { this.renderAuthenticationError() } */}

			</div>

		);
	}
}







Signup.propTypes = {
	
	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.oneOfType([
	    React.PropTypes.oneOf([null]),
	    React.PropTypes.object
	]),
	err: React.PropTypes.object,
	auth: React.PropTypes.object,
	actions: React.PropTypes.object,
	handleSubmit: React.PropTypes.func
};






export default Signup;

