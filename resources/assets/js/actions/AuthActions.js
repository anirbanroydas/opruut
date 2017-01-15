import axios from 'axios';
import { browserHistory } from 'react-router';
import { SubmissionError } from 'redux-form';

export const UPDATE_GLOBAL_REQUESTS = 'UPDATE_GLOBAL_REQUESTS';
export const AUTH_USER = 'AUTH_USER';
export const AUTH_ERROR = 'AUTH_ERROR';
export const SIGN_OUT_USER = 'SIGN_OUT_USER';







export function updateGlobalRequests(globalRequests) {
	// console.log('Action: AuthActions : updateGlobalRequests, arguement : (globalRequests) : ', globalRequests);
	
	globalRequests = parseInt(globalRequests);

	return {
		type: UPDATE_GLOBAL_REQUESTS,
		globalRequests
	}
}






function authUser(userinfo) {
	
	return {
		type: AUTH_USER,
		userinfo
	};
}



function authError(error) {
	
	return {
		type: AUTH_ERROR,
		error: error
	};
}



function logoutUser() {
	return {
		type: SIGN_OUT_USER
	};
}








export function signUpUser(credentials) {

	credentials.username = credentials.email.slice(0, credentials.email.indexOf('@'));
	
	// console.log('credentials : ', credentials);

	return function(dispatch) {

		axios.post('/register', credentials)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    dispatch(authUser(response.data));
			browserHistory.push('/');
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			dispatch(authError(error));
		});
		


	};

}





export function signUpUserReduxForm(credentials, dispatch) {

	credentials.username = credentials.email.slice(0, credentials.email.indexOf('@'));
	
	// console.log('credentials : ', credentials);

	return axios.post('/register', credentials)
	.then(function (response) {
	    
	    // console.log('response : ',response);
	    dispatch(authUser(response.data));
		browserHistory.push('/');
	
	})
	.catch(function (error) {
	    // console.log('ERROR : ', error);
		dispatch(authError(error));
		if (error.response && error.response.data) {

			let errors = {};

			if (error.response.data.password) {
				errors = {...errors, password: 'Password must have atleast 6 characters'};
			}
			if (error.response.data.email) {
				errors = {...errors, email: 'Email is already used!'};
			}
			if (error.response.data.name) {
				errors = {...errors, name: error.response.data.name[0]};
			}
			if (error.response.data.gender) {
				errors = {...errors, gender: 'Gender is required'};
			}

			errors = {...errors, _error: 'Signup failed!'};

			throw new SubmissionError(errors);
		}
	});

}



export function signInUser(credentials) {
	
	// console.log('credentials : ', credentials);

	return function(dispatch) {
		
		axios.post('/login', credentials)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    dispatch(authUser(response.data));
			browserHistory.push('/');
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			dispatch(authError(error));
		});



	};
}







export function signInUserReduxForm(credentials, dispatch) {
	
	// console.log('credentials : ', credentials);

	return axios.post('/login', credentials)
	.then(function (response) {
	    
	    // console.log('response : ',response);
	    dispatch(authUser(response.data));
		browserHistory.push('/');
	
	})
	.catch(function (error) {
	    // console.log('ERROR : ', error);
		dispatch(authError(error));
		if (error.response && error.response.data) {

			if (error.response.data.password || error.response.data.email) {
				throw new SubmissionError({ password: 'Username or Password is wrong', email: 'Username or Password is wrong', _error: 'Login failed!' });
			}
			
		}
	});
}






export function signOutUser() {
	return function(dispatch) {

		axios.post('/logout')
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    dispatch(logoutUser());
			// browserHistory.push('/');
			
			// do a hard refresh to clear all stored state in the store
			// donig a browserHistory push needs a lot of changin the state
			// can be done, needs more code, can be done later
			// for now, an easy but reliable error free solution
			// is to do a hard refresh
			window.location.href = '/';
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			dispatch(authError(error));
		});
	
	};
	

	
}



export function clientValidateSignup(values)  {
	// console.log('clientValidateSignup : values : ', values);

	const errors = {};

	if (!values.name) {
		errors.name = "Please enter an email";
	} 
	else if (!/^[A-Za-z][A-Za-z. ]{0,100}$/i.test(values.name)) {
		errors.name = 'Invalid Name'
	}


	if (!values.email) {
		errors.email = "Please enter an email";
	} 
	else if (!/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(values.email)) {
		errors.email = 'Invalid email address'
	}

	if (!values.password) {
		errors.password = "Please enter a password";
	}
	else if (values.password.length < 6) {
		errors.password = "Password must have atleast 6 characters";
	}

	if (!values.password_confirmation) {
		errors.password_confirmation = "Please enter a password confirmation";
	}

	if (values.password !== values.password_confirmation ) {
		errors.password = 'Passwords do not match';
		errors.password_confirmation = "Passwords do not match"
	}

	if (!values.gender) {
		errors.gender = "Gender is required";
	}

	// console.log('clientValidateSignup : erros : ', errors);


	return errors;
}






export function clientValidateLogin(values) {
	const errors = {};

	if (!values.email) {
		errors.email = "Please enter an email";
	} else if (!/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(values.email)) {
		errors.email = 'Invalid email address'
	}

	if (!values.password) {
		errors.password = "Please enter a password";
	}

	return errors;
}






export function serverAsyncValidateSignup(values, dispatch) {

	// console.log('Action: AuthActions : serverAsyncValidateSignup, arguement : (values) : ', values);

	return axios.post(`/api/v1/validate/email/allowed?email=${values.email}`)
	.then(function (response) {
	    
	    // console.log('response : ',response);
	    if (response.data.isAllowed !== true) {
	    	throw {'email': 'Email already used!'}
	    }
	
	})
	.catch(function (error) {
	    // console.log('ERROR : ', error);
	    if (error.response && error.response.data && error.response.data.email) {

	    	throw {'email': 'Email already used!'};
	    }
	    else if (error.email) {
	    	throw {'email': 'Email already used!'};
	    }
	    
	});

}





