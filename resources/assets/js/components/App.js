import React from 'react';
import classnames from 'classnames';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import getMuiTheme from 'material-ui/styles/getMuiTheme';

import NavbarContainer from '../containers/NavbarContainer';

import appStyles from '../../sass/components/app.scss';




class App extends React.Component {
	
	render() {

		return (
			
			<MuiThemeProvider muiTheme={ getMuiTheme() } >
				
				<div className={ classnames(appStyles.app) } >

					<NavbarContainer />
					
					{this.props.children}

				</div>
			
			</MuiThemeProvider>
		);
	}

}



App.propTypes = {

	children: React.PropTypes.object

};



export default App