import { browserHistory } from 'react-router';



export const UPDATE_NAVBAR_QUICK_LINK = 'UPDATE_NAVBAR_QUICK_LINK';



export function updateNavbarQuickLink() {
	// console.log('Action: AuthActions : updateNavbarQuickLink, arguement : () : ');
	
	let quicklink = browserHistory.getCurrentLocation().pathname;

	return {
		type: UPDATE_NAVBAR_QUICK_LINK,
		quicklink
	}
}




