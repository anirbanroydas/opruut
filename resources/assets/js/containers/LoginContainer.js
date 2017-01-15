import React from 'react';
import { connect } from 'react-redux';
import { reduxForm } from 'redux-form';
import { bindActionCreators } from 'redux';

import * as Actions from '../actions';
import Login from '../components/Login';



class LoginContainer extends React.Component {

	render() {
		
		return (
		         
            <Login { ...this.props } />
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
	
	form: 'login',
	validate: Actions.clientValidateLogin

})(LoginContainer));





