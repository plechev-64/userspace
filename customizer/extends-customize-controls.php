<?php

defined( 'ABSPATH' ) || exit;


// here we will collect our implementations that extend the customizer controls


if ( class_exists( 'WP_Customize_Control' ) ) {

	// range - with the display of the selected value
	class USP_Customize_Range extends WP_Customize_Control {
		public $type = 'usp-range';

		public function __construct( $manager, $id, $args = [] ) {
			parent::__construct( $manager, $id, $args );
			$defaults = [
				'min'  => 0,
				'max'  => 10,
				'step' => 1,
			];
			$argums   = wp_parse_args( $args, $defaults );

			$this->min  = $argums['min'];
			$this->max  = $argums['max'];
			$this->step = $argums['step'];
		}

		public function enqueue() {
			// https://css-tricks.com/sliding-nightmare-understanding-range-input/
			wp_enqueue_style(
				'usp-range-css',
				plugins_url( '/assets/css/usp-range.css', __FILE__ ),
				'1.0.0'
			);
		}

		public function render_content() {
			$input_id = '_customize-input-' . $this->id;
			?>

            <div class="usp-range-wrapper">
				<?php if ( isset( $this->label ) ) : ?>
                    <label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
				<?php endif; ?>

				<?php if ( ! empty( $this->description ) ) : ?>
                    <span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php endif; ?>

                <div class="usp-range-slider">
                    <input
                            id="<?php echo esc_attr( $input_id ); ?>"
                            class='range-slider'
                            min="<?php echo $this->min ?>"
                            max="<?php echo $this->max ?>"
                            step="<?php echo $this->step ?>"
                            type='range'
						<?php $this->link(); ?>
                            value="<?php echo esc_attr( $this->value() ); ?>"
                            oninput="jQuery(this).next('input').val( jQuery(this).val() )"
                    >
                    <input
                            class="<?php echo esc_attr( 'usp-range-val usp-range-val-' . $this->id ); ?>"
                            onKeyUp="jQuery(this).prev('input').val( jQuery(this).val() )"
                            type='text'
                            value='<?php echo esc_attr( $this->value() ); ?>'
                            disabled
                    >
                </div>
            </div>
			<?php
		}
	}


	// alpha-color-picker https://github.com/BraadMartin/components
	// author BraadMartin http://braadmartin.com/
	// license GPL
	// your colorpicker with transmitted values of a set of colors and friendly for touch devices (large buttons)
	// iris http://automattic.github.io/Iris/
	class USP_Customize_Color extends WP_Customize_Control {
		public $type = 'usp-color';

		public $palette = [
			'#000000',
			'#ffffff',
			'#D32F2F',
			'#7B1FA2',
			'#303F9F',
			'#1976D2',
			'#00796B',
			'#AFB42B',
			'#FBC02D',
			'#FFA000',
			'#F57C00',
			'#5D4037',
			'#616161',
			'#455A64',
		];

		public function enqueue() {
			wp_enqueue_script(
				'usp-color-picker-js',
				plugins_url( '/assets/js/usp-color-picker.js', __FILE__ ),
				[ 'jquery', 'wp-color-picker' ],
				'1.0.0',
				true
			);
			wp_enqueue_style(
				'usp-color-picker-css',
				plugins_url( '/assets/css/usp-color-picker.css', __FILE__ ),
				[ 'wp-color-picker' ],
				'1.0.0'
			);
		}

		public function render_content() {
			if ( is_array( $this->palette ) ) {
				$palette = implode( '|', $this->palette );
			} else {
				$palette = ( false === $this->palette || 'false' === $this->palette ) ? 'false' : 'true';
			}

			$input_id = '_customize-input-' . $this->id;
			?>
            <div class="usp-color-control-wrapper">

				<?php if ( isset( $this->label ) ) : ?>
                    <label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
				<?php endif; ?>

				<?php if ( ! empty( $this->description ) ) : ?>
                    <span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php endif; ?>

                <input
                        class="usp-color-control"
                        type="text"
                        data-palette="<?php echo esc_attr( $palette ); ?>"
                        data-default-color="<?php echo esc_attr( $this->settings['default']->default ); ?>"
					<?php $this->link(); ?>
                />

            </div>
			<?php
		}
	}


	// Simple separator
	class USP_Customize_Separator extends WP_Customize_Control {
		public $type = 'usp-separator';

		public function render_content() {
			?>
            <hr style="border-color:#ddd;margin:12px 0 0;">
			<?php
		}
	}


	// Note in the customizer
	class USP_Customize_Note extends WP_Customize_Control {
		public $type = 'usp-note';

		public function render_content() {
			$input_id = '_customize-input-' . $this->id;

			if ( isset( $this->label ) ) {
				echo '<label for="' . esc_attr( $input_id ) . '" class="customize-control-title">' . esc_html( $this->label ) . '</label>';
			}

			if ( isset( $this->description ) ) {
				echo '<span class="description customize-control-description">' . wp_kses_post( $this->description ) . '</span>';
			}
		}
	}
    
}
