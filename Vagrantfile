# Defines our Vagrant environment
#
# -*- mode: ruby -*-
# vi: set ft=ruby :

BOX      = 'ubuntu/trusty64'
HOSTNAME = 'backend'
RAM      = 1024
IP       = '192.168.1.200'
CPUS     = 1
CPUCAP   = 95

Vagrant.configure("2") do |config|

    config.vm.box = BOX
    config.vm.hostname = HOSTNAME

    #config.vm.network :private_network, ip: IP
    config.vm.network "private_network", type: "dhcp"

    config.vm.provider "virtualbox" do |vb|
      vb.memory = RAM
    end
    config.vm.network :forwarded_port, guest: 80, host: 8000, auto_correct: true
    config.vm.network :forwarded_port, guest: 3306, host: 13306, auto_correct: true
    #config.vm.synced_folder ".", "/var/www/html", create: true, group: "www-data", owner: "www-data"
    
    config.vm.synced_folder ".", "/vagrant", nfs: true

	#config.ssh.forward_agent = true

	config.vm.provider "virtualbox" do |vm|
        vm.customize ["modifyvm", :id, "--memory", RAM]
        vm.customize ["modifyvm", :id, "--cpus", CPUS]
        vm.customize ["modifyvm", :id, "--cpuexecutioncap", CPUCAP]
    end

    config.vm.provision :shell, path: "vagrant/provision.sh"
end
