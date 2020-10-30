# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

	# It seems to need this here or "destroy" errors.
	config.vm.box = "ubuntu/focal64"

	config.vm.define "app" do |app|

		app.vm.box = "ubuntu/focal64"

		app.vm.network "forwarded_port", guest: 8025, host: 8025
		app.vm.network "forwarded_port", guest: 8080, host: 8080

		app.vm.synced_folder ".", "/vagrant",  :type=> 'rsync', :rsync__args => ["--verbose", "--archive", "-z", "--copy-links"] , :owner=> 'vagrant', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

		app.vm.provider "virtualbox" do |vb|
			# Display the VirtualBox GUI when booting the machine
			vb.gui = false

			# Customize the amount of memory on the VM:
			vb.memory = "4096"
		end

		app.vm.provision :shell, path: "vagrant/app/bootstrap.sh"

	end


end
