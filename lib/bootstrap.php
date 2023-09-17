<?php

define( 'JDBSTART', microtime( true ));

spl_autoload_register( function( $nm )
{
	if( strpos( $nm, 'JDB\\' ) === false )
	{
		return;
	}
	
	$nm = implode( DIRECTORY_SEPARATOR, array_slice( explode( '\\', $nm ), 1 ));
	include_once __DIR__ . DIRECTORY_SEPARATOR . $nm . '.php';
});
