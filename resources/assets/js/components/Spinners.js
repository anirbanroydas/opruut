import React, { Component } from 'react';
import classnames from 'classnames';

import spinnersStyles from '../../sass/components/spinners.scss';



class Spinners extends Component {

	
	renderSpinner(type) {

		// let clasname = classnames()
		// let classes = {
			
		// 	heightStyle: this.props.style.heightStyle,
		// 	widthStyle: this.props.style.widthStyle,
		// 	colorStyle: this.props.style.colorStyle

		// };

		

		switch (type) {

			case 'wave':

				return (
					<div  className={ classnames(spinnersStyles.wave) } style={ this.props.style }  >
					  	<div  className={ classnames(spinnersStyles.rect1, ) } ></div>
					  	<div  className={ classnames(spinnersStyles.rect2) } ></div>
					  	<div  className={ classnames(spinnersStyles.rect3) } ></div>
					  	<div  className={ classnames(spinnersStyles.rect4) } ></div>
					  	<div  className={ classnames(spinnersStyles.rect5) } ></div>
					</div>

				);

			case 'bubbles':

				return (

					<div className={ classnames(spinnersStyles.bubbles) } >
					  	<div className={ classnames(spinnersStyles.bounce1) } ></div>
					  	<div className={ classnames(spinnersStyles.bounce2) } ></div>
					  	<div className={ classnames(spinnersStyles.bounce3) } ></div>
					</div>
				);


			case 'wandering-cubes':

				return (

					<div className={ classnames(spinnersStyles.wanderingCubes) } >
					  	<div className={ classnames(spinnersStyles.wanderingCube1) } ></div>
					  	<div className={ classnames(spinnersStyles.wanderingCube2) } ></div>
					</div>
				);


			case 'chasing-dots':

				return (

					<div className={ classnames(spinnersStyles.chasingDots) } >
					  	<div className={ classnames(spinnersStyles.dot1) } ></div>
					  	<div className={ classnames(spinnersStyles.dot2) } ></div>
					</div>
				);


			case 'rotating-plane':

				return (

					<div className={ classnames(spinnersStyles.rotatingPlane) } ></div>
				);


			case 'loading-circles':

				return (

					<div className={ classnames(spinnersStyles.loadingCircles) } >
						<div className={ classnames(spinnersStyles.loadingCircle1, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle2, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle3, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle4, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle5, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle6, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle7, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle8, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle9, spinnersStyles.loadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingCircle10, spinnersStyles.loadingCircle) } ></div>
					   	<div className={ classnames(spinnersStyles.loadingCircle11, spinnersStyles.loadingCircle) } ></div>
					   	<div className={ classnames(spinnersStyles.loadingCircle12, spinnersStyles.loadingCircle) } ></div>
					</div>
				);


			case 'loading-fading-circles':

				return (

					<div className={ classnames(spinnersStyles.loadingFadingCircles) } >
						<div className={ classnames(spinnersStyles.loadingFadingCircle1, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle2, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle3, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle4, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle5, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle6, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle7, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle8, spinnersStyles.loadingFadingCircle) } ></div>
						<div className={ classnames(spinnersStyles.loadingFadingCircle9, spinnersStyles.loadingFadingCircle) } ></div>
					   	<div className={ classnames(spinnersStyles.loadingFadingCircle10, spinnersStyles.loadingFadingCircle) } ></div>
					   	<div className={ classnames(spinnersStyles.loadingFadingCircle11, spinnersStyles.loadingFadingCircle) } ></div>
					   	<div className={ classnames(spinnersStyles.loadingFadingCircle12, spinnersStyles.loadingFadingCircle) } ></div>
					</div>
				);


			case 'cube-grid':

				return (

					<div className={ classnames(spinnersStyles.cubeGrid) } >
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube1) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube2) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube3) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube4) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube5) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube6) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube7) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube8) } ></div>
						<div className={ classnames(spinnersStyles.cube, spinnersStyles.cube9) } ></div>
					</div>			

				);



			case 'cylon':

				return (

					<div className={ classnames(spinnersStyles.cylon) } >	
						<div  className={ classnames(spinnersStyles.cylonRect1, spinnersStyles.cylonRect) } ></div>
					  	<div  className={ classnames(spinnersStyles.cylonRect2, spinnersStyles.cylonRect) } ></div> 
					  	<div  className={ classnames(spinnersStyles.cylonRect3, spinnersStyles.cylonRect) } ></div> 
					</div>
				);


			case 'cylon-svg':

				return (

					<div className={ classnames(spinnersStyles.cylonSvg) } >	
						<svg  viewBox="0 0 300 30">
						  <path transform="translate(0 0)" d="M0 7 V22 H5 V5z">
						    <animateTransform attributeName="transform" type="translate" values="0 0; 298 0; 0 0; 0 0" dur="1.5s" begin="0" repeatCount="indefinite" keyTimes="0;0.3;0.6;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" calcMode="spline" />
						  </path>
						  <path opacity="0.5" transform="translate(0 0)" d="M0 7 V22 H5 V7z">
						    <animateTransform attributeName="transform" type="translate" values="0 0; 298 0; 0 0; 0 0" dur="1.5s" begin="0.1s" repeatCount="indefinite" keyTimes="0;0.3;0.6;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" calcMode="spline" />
						  </path>
						  <path opacity="0.25" transform="translate(0 0)" d="M0 7 V22 H5 V7z">
						    <animateTransform attributeName="transform" type="translate" values="0 0; 298 0; 0 0; 0 0" dur="1.5s" begin="0.2s" repeatCount="indefinite" keyTimes="0;0.3;0.6;1" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" calcMode="spline" />
						  </path>
						</svg>
					</div>
				);

		}
	
	}





	render() {
		
		return ( 
			
			<div>
				{ this.renderSpinner(this.props.type) }
			</div>
		 );
		
	} 
}


Spinners.propTypes = {

	type: React.PropTypes.string,
	style: React.PropTypes.object
	// height: React.PropTypes.number,
	// width: React.PropTypes.number,
	// speed: React.PropTypes.float
}


// Spinner2.defaultProps = {

// 	type: React.PropTypes.string,
// 	height: React.PropTypes.number,
// 	width: React.PropTypes.number,
// 	speed: React.PropTypes.float
// }


export default Spinners;
