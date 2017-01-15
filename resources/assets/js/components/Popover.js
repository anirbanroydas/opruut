import React, { Component, PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { Link } from 'react-router';
import classnames from 'classnames';

import IconButton from 'material-ui/IconButton';
import FavoriteBorderIcon from 'material-ui/svg-icons/action/favorite-border';

import opruutHeartStyles from '../../sass/components/opruutHeart.scss';
import buttonStyles from '../../sass/components/buttons.scss';

import popoverStyles from '../../sass/components/popover.scss';


const propTypes = {
	favorites_count: PropTypes.number,
	className: PropTypes.string,
	// children: PropTypes.node.isRequired,
};



let customIconButtonStyle = {
	border: "5px",
	padding: "0px",
	width: "30px",
	height: "30px",
	marginLeft: "7px"
}




class Popover extends Component {
	
	constructor(props) {
		super(props);
		
		// if (props.children.length > 1) {
		// 	throw new Error('Popover component requires exactly 2 children');
		// }

		this.onOutsideClick = this.onOutsideClick.bind(this);
		this.toggleIsOpen = this.toggleIsOpen.bind(this);

		this.state = { isOpen: false };
	}



	componentDidMount() {
		document.addEventListener('mousedown', this.onOutsideClick);
	}



	componentWillUnmount() {
		document.removeEventListener('mousedown', this.onOutsideClick);
	}




	onOutsideClick(e) {
		
		if (!this.state.isOpen) {
			return;
		}

		e.stopPropagation();
		const localNode = ReactDOM.findDOMNode(this);
		let source = e.target;

		while (source.parentNode) {
			if (source === localNode) {
				return;
			}
			source = source.parentNode;
		}

		this.setState({
			isOpen: false,
		});
	}




	toggleIsOpen(e) {
		e.preventDefault();

		this.setState({ isOpen: !this.state.isOpen });
	}



	renderHearts() {
		let loves = this.props.favorites_count;

		if (!loves) {
			return 'Be the first one to love';
		}
		else {
			return `${loves} loves`
		}
	}




	renderPopover() {

		if (this.state.isOpen) {
			return (
				<div className={ classnames(opruutHeartStyles.opruutHeartPopover, popoverStyles.popoverContent) } >
					<ul className={ classnames(popoverStyles.navUserPopoverList) } >
						<li className={ classnames(popoverStyles.navUserPopoverItem) } >
							<Link to="/login" className={ classnames(buttonStyles.button, buttonStyles.customBlue, buttonStyles.block) } >  
								Sign into OpRuut
							</Link>
						</li>
					</ul>
				</div>
			);
		}
		else {
			return null;
		}
	}


	render() {

		return (
			<div
				className={ classnames(this.props.className,  popoverStyles.popover, { 'open' : this.state.isOpen  } ) }
			>

				<div className={classnames(opruutHeartStyles.heartCountContainer)} >
					<IconButton onTouchTap={this.toggleIsOpen}  style={customIconButtonStyle} >
				      	<FavoriteBorderIcon />
				    </IconButton>
				    <p className={classnames(opruutHeartStyles.heartCount)}> { this.renderHearts() } </p>
			   	</div>

			   	
			   	{ this.renderPopover() }
			   	

				{/* {this.state.isOpen ? this.props.children[0] : null} */}
			
			</div>
		);
	}
}

Popover.propTypes = propTypes;

export default Popover;
