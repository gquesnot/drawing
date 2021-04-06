# drawing

drawing is a website which uses laravel and view.js to create an advanced "paint" by manipulating canvas thanks to the library [fabric.js](http://fabricjs.com/) 

# Fonctionality
    -free drawing
    -replay
    -layout using a drawing you did
   

## Installation
	config .env
    
```bash
	composer install
	npm install
	php artisan migrate	
	php artisan optimize:clear
```

## Usage
```bash
	 npm run dev && php artisan serve --port=80
```
or for live editing these 2 commands in different shells 
```bash
	npm run watch
	php artisan serve --port=80
```
