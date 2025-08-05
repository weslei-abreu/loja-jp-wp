import { __ } from '@wordpress/i18n';
import { PriceHtml } from '@dokan/components';
import { Tooltip } from '@getdokan/dokan-ui';
import { twMerge } from 'tailwind-merge';

function Discount( { discountTotal }: { discountTotal: number } ) {
    if ( discountTotal < 1 ) {
        return null;
    }

    return (
        <div className="flex justify-between px-4">
            <div>
                { /* eslint-disable-next-line jsx-a11y/label-has-associated-control */ }
                <label className="text-sm text-gray-500 mb-1">
                    { __( 'Discount', 'dokan' ) }
                </label>
                &nbsp;
                <Tooltip
                    content={ __(
                        'This is the total discount. Discounts are defined per line item.',
                        'dokan'
                    ) }
                    direction="top"
                    contentClass={ twMerge(
                        '',
                        'bg-gray-800 text-white p-2 rounded-md'
                    ) }
                >
                    <span className="fa fa-question-circle dokan-vendor-order-page-tips text-sm"></span>
                </Tooltip>
                :
            </div>

            <span className="flex">
                - &nbsp;
                <PriceHtml price={ discountTotal } />
            </span>
        </div>
    );
}

export default Discount;
