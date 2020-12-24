# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

    # It seems to need this here or "destroy" errors.
    config.vm.box = "ubuntu/focal64"

    config.vm.define "app1" do |app|

        app.vm.box = "ubuntu/focal64"

        app.vm.network "private_network", ip: "192.168.50.11"

        app.vm.synced_folder ".", "/vagrant",  :type=> 'rsync', :rsync__args => ["--verbose", "--archive", "-z", "--copy-links"] , :owner=> 'vagrant', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

        app.vm.provider "virtualbox" do |vb|
            # Display the VirtualBox GUI when booting the machine
            vb.gui = false

            # Customize the amount of memory on the VM:
            vb.memory = "2048"
        end

        app.vm.provision "file", source: "~/.ssh/id_rsa.pub", destination: "/home/vagrant/.ssh/me.pub"

        app.vm.provision :shell, path: "vagrant/app/bootstrap.sh"

    end

    config.vm.define "app2" do |app|

        app.vm.box = "ubuntu/focal64"

        app.vm.network "private_network", ip: "192.168.50.12"

        app.vm.synced_folder ".", "/vagrant",  :type=> 'rsync', :rsync__args => ["--verbose", "--archive", "-z", "--copy-links"] , :owner=> 'vagrant', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

        app.vm.provider "virtualbox" do |vb|
            # Display the VirtualBox GUI when booting the machine
            vb.gui = false

            # Customize the amount of memory on the VM:
            vb.memory = "2048"
        end

        app.vm.provision "file", source: "~/.ssh/id_rsa.pub", destination: "/home/vagrant/.ssh/me.pub"

        app.vm.provision :shell, path: "vagrant/app/bootstrap.sh"

    end

end
