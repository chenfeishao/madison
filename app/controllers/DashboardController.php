<?php
/**
 * 	Controller for admin dashboard
 */
class DashboardController extends BaseController{
	
	public $restful = true;
	
	public function __construct(){
		//Filter to ensure user is signed in and is user_level == 1
		$this->beforeFilter('admin');
		
		//Run csrf filter before all posts
		$this->beforeFilter('csrf', array('on'=>'post'));
		
		parent::__construct();
	}
	
	/**
	 * 	Dashboard Index View
	 */
	public function getIndex(){
		$data = array(
			'page_id'		=> 'dashboard',
			'page_title'	=> 'Dashboard'
		);

		return View::make('dashboard.index', $data);
	}

	/**
	 * 	Document Creation/List or Document Edit Views
	 */
	public function getDocs($id = ''){
		if($id == ''){
			$docs = Doc::all();

			$data = array(
				'page_id'		=> 'doc_list',
				'page_title'	=> 'Edit Documents',
				'docs'			=> $docs
			);

			return View::make('dashboard.docs', $data);
		}
		else{
			$doc = Doc::find($id);
			if(isset($doc)){
				$data = array(
					'page_id'		=> 'edit_doc',
					'page_title'	=> 'Edit ' . $doc->title,
					'doc'			=> $doc,
					// Just get the first content element.  We only have one, now.
					'contentItem' => $doc->content()->where('parent_id')->first()
				);

				return View::make('dashboard.edit-doc', $data);
			}
			else{
				return Response::error('404');
			}
		}
	}
	
	/**
	 * 	Post route for creating / updating documents
	 */
	public function postDocs($id = ''){
		//Creating new document
		if($id == ''){
			$title = Input::get('title');
			$slug = Input::get('slug');
			$doc_details = Input::all();

			$rules = array('title' => 'required',
							'slug' => 'required|unique:docs'
							);
			$validation = Validator::make($doc_details, $rules);
			if($validation->fails()){
				return Redirect::to('dashboard/docs')->with_input()->with_errors($validation);
			}

			try{
				$doc = new Doc();
				$doc->title = $title;
				$doc->slug = $slug;
				$doc->save();

				$starter = new DocContent();
				$starter->doc_id = $doc->id;
				$starter->content = "New Doc Content";
				$starter->save();

				$doc->init_section = $starter->id;
				$doc->save();

				return Redirect::to('dashboard/docs/' . $doc->id)->with('success_message', 'Document created successfully');
			}catch(Exception $e){
				return Redirect::to('dashboard/docs')->with_input()->with('error', $e->getMessage());
			}
		}
		else{
			return Response::error('404');
		}
	}

	/**
	 * 	PUT route for saving documents
	 */
	public function putDocs($id = ''){
		$content = Input::get('content');
		$content_id = Input::get('content_id');

		if($content_id){
			try{
				$doc_content = DocContent::find($content_id);
			}catch (Exception $e){
				return Redirect::to('dashboard/docs/' . $id)->with('error', 'Error saving the document: ' . $e->getMessage());
			}
		}
		else{
			$doc_content = new DocContent();
		}

		$doc_content->doc_id = $id;
		$doc_content->content = $content;
		$doc_content->save();

		$doc = Doc::find($id);
		$doc->store_content($doc, $doc_content);

		return Redirect::to('dashboard/docs/' . $id)->with('success_message', 'Document Saved Successfully');
	}

	/**
	 * 	POST route for adding line items to documents
	 * 		Returns JSON array that includes the new line item's auto-incremented id
	 */
	public function postContent($id = ''){

		if($id != ''){
			return Response::error('404');
		}
		$content_details = Input::all();

		$rules = array('doc_id' => 'required',
						'content' => 'required',
						'parent_id' => 'required'
						);
		$validation = Validator::make($content_details, $rules);
		if($validation->fails()){
			return json_encode(array('success'=>false, 'msg'=>'New content failed required fields'));
		}
		
		$doc_id = Input::get('doc_id');
		$content = Input::get('content');
		$parent_id = Input::get('parent_id');
		$child_priority = Input::get('child_priority');
		
		$doc_item = new DocContent();
		$doc_item->doc_id = $doc_id;
		$doc_item->content = $content;
		$doc_item->parent_id = $parent_id;
		$doc_item->child_priority = 0;
		
		try{
			$doc_item->save();
		}catch(Exception $e){
			return json_encode(array('success'=>false, 'msg'=>'Failure saving new content: ' . $e->getMessage()));
		}
		
		return json_encode(array('success'=>true, 'id' => $doc_item->id, 'msg' => 'Content created successfully'));
	}
	
	/**
	 * 	Verification request view
	 */
	public function getVerifications(){
		$data = array(
			'page_id'		=> 'verify_users',
			'page_title'	=> 'Verify Users'
		);

		return View::make('dashboard.verify-account', $data);
	}

	/**
	 * 	Post route for handling verification request responses
	 */
	public function postVerification(){

	}



	/**
	 * 	Post route for House document import
	 */
	public function postImport(){
		$url = Input::get('url');
		$import_details = Input::all();

		$rules = array('url' => 'required');

		$validation = Validator::make($import_details, $rules);

		if($validation->fails()){
			return Redirect::back()->with_input()->with_errors($validation);
		}


		try{
			$importer = new BillImport($url);//Found in the library folder ( used specifically for Federal House bills )
			$importer->createDoc();
		}catch(Exception $e){
			return Redirect::back()->with_input()->with('error', 'Error: ' . $e->getMessage());
		}

		return Redirect::back()->with('success_message', "Document created successfully!");
	}
}

