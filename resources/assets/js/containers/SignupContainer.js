import React from 'react';
import { connect } from 'react-redux';
import { reduxForm } from 'redux-form';
import { bindActionCreators } from 'redux';

import * as Actions from '../actions';
import Signup from '../components/Signup';



class SignupContainer extends React.Component {

	render() {
		
		return (
		         
            <Signup { ...this.props } />
		);
	}

}






function mapStateToProps(state) {

	let { auth } = state; 
	let { authenticated, userinfo, error } = auth;

	return {
		authenticated,
		userinfo,
		err: error
	};

}



function mapDispatchToProps(dispatch) {
	
	return {
		actions: bindActionCreators(Actions, dispatch)
	};
}



export default connect(mapStateToProps, mapDispatchToProps)(reduxForm({
	
	form: 'signup',
	validate: Actions.clientValidateSignup,
	asyncBlurFields: [ 'email' ],
	asyncValidate: Actions.serverAsyncValidateSignup


})(SignupContainer));





