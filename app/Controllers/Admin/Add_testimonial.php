<?php
//by leena
class Add_testimonial extends  Controller
{
	public function addTestimonial()
	{
		$clientarray=$this->Common_model->getClients('client_profile');
		$data['clientlist']=$clientarray;
		echo view('admin/testimonial\add_testimonial',$data);

	}
}

?>