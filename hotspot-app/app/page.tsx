"use client";

import { ReactNode, useState } from "react";
import Image from "next/image";
import "./globals.css";

interface Country {
  id: number;
  title: string;
  copy: ReactNode;
  position: { top: string; left: string };
  flag: string;
}

const countries: Country[] = [
  {
    id: 1,
    title: "South Africa",
    copy: (
      <div className="space-y-4">
        <p>
          <span className="italic">South Africa</span> is a country located at
          the southern tip of the African continent. It is situated entirely
          within the Southern Hemisphere and is bordered to the north by{" "}
          <span className="italic">Namibia</span>,{" "}
          <span className="italic">Botswana</span>, and{" "}
          <span className="italic">Zimbabwe</span>, to the northeast by{" "}
          <span className="italic">Mozambique</span> and{" "}
          <span className="italic">Eswatini</span>, and it surrounds the kingdom
          of <span className="italic">Lesotho</span>. It is flanked by the
          Atlantic Ocean to the southwest and the Indian Ocean to the southeast.
        </p>
        <p>The tourist attraction is Table Mountain.</p>
        <Image
          src="/table-mtn.jpg"
          alt="table-mtn"
          className="object-contain object-center"
          width={300}
          height={300}
        />
      </div>
    ),
    position: { top: "89%", left: "64%" },
    flag: "/flags/ZA.png",
  },
  {
    id: 2,
    title: "Angola",
    copy: (
      <p>
        Angola is a country located in the southwestern corner of Africa. Known
        for its diverse landscapes - from the Namib Desert's otherworldly dunes
        to the savannahs and the towering peaks of the Brandberg Mountains -
        Angola is a destination that captivates the senses. Its vast, open
        spaces, rich cultural heritage, and vibrant wildlife make it a truly
        unforgettable experience.
      </p>
    ),
    position: { top: "67%", left: "57%" },
    flag: "/flags/AO.png",
  },
  {
    id: 3,
    title: "Namibia",
    copy: (
      <p>
        Namibia, a land of stark beauty and untamed wilderness, is a country
        located in the southwestern corner of Africa. Known for its diverse
        landscapes - from the Namib Desert's otherworldly dunes to the savannahs
        and the towering peaks of the Brandberg Mountains - Namibia is a
        destination that captivates the senses. Its vast, open spaces, rich
        cultural heritage, and vibrant wildlife make it a truly unforgettable
        experience.
      </p>
    ),
    position: { top: "80%", left: "56%" },
    flag: "/flags/NA.png",
  },
  {
    id: 4,
    title: "Zimbabwe",
    copy: (
      <p>
        Zimbabwe, a land of breathtaking beauty and untamed wilderness, is a
        country located in the southeastern corner of Africa. Known for its
        diverse landscapes - from the majestic Victoria Falls to the rolling
        hills of the Matobo National Park - Zimbabwe is a destination that
        captivates the senses. Its rich cultural heritage, vibrant wildlife, and
        unique wildlife make it a truly unforgettable experience.
      </p>
    ),
    position: { top: "75%", left: "71%" },
    flag: "/flags/ZW.png",
  },
  {
    id: 5,
    title: "Botswana",
    copy: (
      <p>
        Botswana, a land of breathtaking beauty and untamed wilderness, is a
        country located in the southeastern corner of Africa. Known for its
        diverse landscapes - from the majestic Victoria Falls to the rolling
        hills of the Matobo National Park - Botswana is a destination that
        captivates the senses. Its rich cultural heritage, vibrant wildlife, and
        unique wildlife make it a truly unforgettable experience.
      </p>
    ),
    position: { top: "78%", left: "64%" },
    flag: "/flags/BW.png",
  },
];

export default function Home() {
  const [selectedCountry, setSelectedCountry] = useState<number>(1);

  const handleHotspotClick = (countryId: number) => {
    setSelectedCountry(countryId);
  };

  const selectedCountryData =
    countries.find((country) => country.id === selectedCountry) || countries[0];

  return (
    <div className="min-h-screen bg-gray-50 p-8">
      <div className="max-w-7xl mx-auto">
        <h1 className="page-heading">Countries to visit in Africa</h1>
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* first column: Map with Hotspots using country flags */}
          <div className="relative bg-white rounded-lg shadow-lg overflow-hidden">
            <div className="relative w-full h-[600px]">
              {/* image of map */}
              <Image
                src="/map.jpg"
                alt="Map"
                fill
                className="map-image"
                priority
              />

              {/* Flag Icons */}
              {countries.map((country) => (
                <button
                  key={country.id}
                  onClick={() => handleHotspotClick(country.id)}
                  className="flag-icon"
                  style={{
                    top: country.position.top,
                    left: country.position.left,
                  }}
                  aria-label={`Select ${country.title}`}
                >
                  <div
                    className={`relative w-12 h-8 rounded transition-all duration-300 ${
                      selectedCountry === country.id ? "scale-125" : ""
                    }`}
                  >
                    <Image
                      src={country.flag}
                      alt={`${country.title} flag`}
                      fill
                      className="flag-icon-item"
                    />
                  </div>
                </button>
              ))}
            </div>
          </div>

          {/* Column 2: Country Information */}
          <div className="bg-white rounded-lg shadow-lg p-8">
            <div className="space-y-6">
              <div className="border-b border-gray-200 pb-4">
                <h2 className="text-2xl font-bold text-gray-900 mb-2">
                  {selectedCountryData.title}{" "}
                </h2>
                <div className="text-gray-600 leading-relaxed">
                  {selectedCountryData.copy}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
