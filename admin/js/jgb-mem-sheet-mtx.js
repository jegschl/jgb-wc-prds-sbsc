class JGBMemSheetMtx{
	#tabedText = '';
	#linesCount = 0;
	#lines = [];
	#colsCount = 0;
	#mtx = [];

	constructor( tabedText = null ){
		if( tabedText != null ){
		 	this.read( tabedText );
		}
	}

	read( tabedText ){
		this.#tabedText = tabedText;
		this.processTabedText();
	}

	processTabedText(){
		let i;

		this.#lines = this.#tabedText.split("\n");
		this.#linesCount = this.#lines.length;
		this.#colsCount = this.#lines[0].split("\t").length;

		
		for( i = 0; i < this.#linesCount; i++ ){
			
			this.#mtx[i] = this.#lines[i].split("\t");
				
		}

		return this.#mtx;
	}

	getMatrix(){
		return this.#mtx;
	}

	getJson(){
		return JSON.stringify( this.#mtx );
	}
}