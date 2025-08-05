import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Card, useToast } from '@getdokan/dokan-ui';
import { twMerge } from 'tailwind-merge';
import PricingCard from './PricingCard';
import { TypeSubscriptionPacks } from '../definition/SubscriptonPack';
import SubscriptionPacksSkeleton from './skeleton/SubscriptionPacksSkeleton';
import { SubscriptionPackProduct } from '../definition/SubscriptionPackProduct';

const SubscriptionPacks = ( {
    subscription,
    className = '',
    hasManagePermission,
} ) => {
    const toast = useToast();
    const [ loading, setLoading ] = useState( true );
    const [ packages, setPackages ] = useState( [] );
    const [ currentPackProduct, setCurrentPackProduct ] = useState( null );

    useEffect( () => {
        const fetchPackages = async () => {
            setLoading( true );

            try {
                const response: TypeSubscriptionPacks = await apiFetch( {
                    path: 'dokan/v1/vendor-subscription/packages?per_page=100',
                    method: 'GET',
                } );

                if ( response.length ) {
                    setPackages( response );
                }
            } catch ( error ) {
                toast( {
                    type: 'error',
                    title:
                        __( 'Error fetching subscription packages:', 'dokan' ) +
                        error?.message,
                } );
            } finally {
                setLoading( false );
            }
        };

        fetchPackages();
    }, [] );

    useEffect( () => {
        if ( ! subscription?.subscription_id ) {
            return;
        }

        const fetchCurrentPackageProduct = async () => {
            const response: SubscriptionPackProduct = await apiFetch( {
                path: `dokan/v1/products/${ subscription.subscription_id }`,
                method: 'GET',
            } );

            if ( ! response.id ) {
                return;
            }

            setCurrentPackProduct( response );
        };

        fetchCurrentPackageProduct();
    }, [ subscription ] );

    return (
        <div className={ twMerge( 'dokan-layout', className ) }>
            <Card className="mb-5">
                <Card.Header>
                    <Card.Title>
                        { __( 'Subscription Packages', 'dokan' ) }
                    </Card.Title>
                </Card.Header>
                <Card.Body>
                    <>
                        { loading ? (
                            <SubscriptionPacksSkeleton />
                        ) : packages.length ? (
                            <div className="grid md:grid-cols-2 gap-5">
                                { packages.map( ( pack ) => (
                                    <PricingCard
                                        key={ pack.id }
                                        pack={ pack }
                                        currentSubscription={ subscription }
                                        hasManagePermission={
                                            hasManagePermission
                                        }
                                        currentPackProduct={
                                            currentPackProduct
                                        }
                                    />
                                ) ) }
                            </div>
                        ) : (
                            <p>
                                { __( 'No subscription pack found.', 'dokan' ) }
                            </p>
                        ) }
                    </>
                </Card.Body>
            </Card>
        </div>
    );
};

export default SubscriptionPacks;
